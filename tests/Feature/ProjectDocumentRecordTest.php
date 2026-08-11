<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectDocumentRecordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_document_record_index_with_documents(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'In Execution Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $project->documents()->create([
            'original_name' => 'report.pdf',
            'file_path' => 'project-documents/1/report.pdf',
            'file_type' => 'pdf',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.document-record.index', $project));

        $response->assertStatus(200);
        $response->assertSee('Expediente Documental');
        $response->assertSee('report.pdf');
        $response->assertSee('PDF');
        $response->assertSee('Subido por:');
    }

    public function test_document_record_index_when_no_documents(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'In Execution Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.document-record.index', $project));

        $response->assertStatus(200);
        $response->assertSee('No hay documentos adjuntos para este proyecto.');
    }

    public function test_document_record_index_accessible_in_pending_status(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Pending Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.document-record.index', $project));

        $response->assertStatus(200);
        $response->assertSee('Expediente Documental');
    }

    public function test_document_record_index_accessible_in_approved_status(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Approved Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.document-record.index', $project));

        $response->assertStatus(200);
        $response->assertSee('Expediente Documental');
    }

    public function test_document_record_index_accessible_in_closed_status(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Closed Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.document-record.index', $project));

        $response->assertStatus(200);
        $response->assertSee('Expediente Documental');
    }

    public function test_document_record_download(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'In Execution Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $file = UploadedFile::fake()->create('report.pdf', 100);
        $path = $file->store("project-documents/{$project->id}", 'public');

        $document = $project->documents()->create([
            'original_name' => 'report.pdf',
            'file_path' => $path,
            'file_type' => 'pdf',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.document-record.download', [$project, $document]));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=report.pdf');
    }

    public function test_document_record_download_allowed_when_project_not_in_progress(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Pending Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $file = UploadedFile::fake()->create('report.pdf', 100);
        $path = $file->store("project-documents/{$project->id}", 'public');

        $document = $project->documents()->create([
            'original_name' => 'report.pdf',
            'file_path' => $path,
            'file_type' => 'pdf',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.document-record.download', [$project, $document]));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=report.pdf');
    }

    public function test_document_record_download_fails_if_document_does_not_belong_to_project(): void
    {
        $admin = User::first();

        $project1 = Project::create([
            'title' => 'Project 1',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $project2 = Project::create([
            'title' => 'Project 2',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $file = UploadedFile::fake()->create('doc1.pdf', 100);
        $path = $file->store("project-documents/{$project1->id}", 'public');

        $document = $project1->documents()->create([
            'original_name' => 'doc1.pdf',
            'file_path' => $path,
            'file_type' => 'pdf',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.document-record.download', [$project2, $document]));

        $response->assertStatus(404);
    }

    public function test_document_record_button_visibility_on_project_show_regardless_of_status(): void
    {
        $admin = User::first();

        $projectPending = Project::create([
            'title' => 'Pending Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $projectExecution = Project::create([
            'title' => 'Execution Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $projectClosed = Project::create([
            'title' => 'Closed Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $projectExecution));
        $response->assertSee('Ver Expediente Documental');

        $response = $this->actingAs($admin)->get(route('projects.show', $projectPending));
        $response->assertSee('Ver Expediente Documental');

        $response = $this->actingAs($admin)->get(route('projects.show', $projectClosed));
        $response->assertSee('Ver Expediente Documental');
    }
}
