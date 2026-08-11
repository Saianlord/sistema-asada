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

class ProjectDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_authorized_user_can_upload_document(): void
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

        $file = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('projects.documents.store', $project), [
            'document' => $file,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Documento adjuntado correctamente.');

        $this->assertDatabaseHas('project_documents', [
            'project_id' => $project->id,
            'original_name' => 'contract.pdf',
            'file_type' => 'pdf',
            'uploaded_by' => $admin->id,
        ]);

        $document = ProjectDocument::first();
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_project_owner_can_upload_document(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('operations');

        $project = Project::create([
            'title' => 'Owner Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $owner->id,
            'status' => 'in_progress',
        ]);

        $file = UploadedFile::fake()->create('owner_doc.pdf', 300);

        $response = $this->actingAs($owner)->post(route('projects.documents.store', $project), [
            'document' => $file,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('project_documents', [
            'project_id' => $project->id,
            'original_name' => 'owner_doc.pdf',
            'uploaded_by' => $owner->id,
        ]);
    }

    public function test_upload_validation_rejects_invalid_file(): void
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

        $invalidFile = UploadedFile::fake()->create('exploit.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($admin)->post(route('projects.documents.store', $project), [
            'document' => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['document']);
        $this->assertDatabaseEmpty('project_documents');

        $oversizedFile = UploadedFile::fake()->create('large.pdf', 20000, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('projects.documents.store', $project), [
            'document' => $oversizedFile,
        ]);

        $response->assertSessionHasErrors(['document']);
        $this->assertDatabaseEmpty('project_documents');
    }

    public function test_authorized_user_can_delete_document(): void
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

        $file = UploadedFile::fake()->create('quote.docx', 200);
        $path = $file->store("project-documents/{$project->id}", 'public');

        $document = $project->documents()->create([
            'original_name' => 'quote.docx',
            'file_path' => $path,
            'file_type' => 'docx',
            'uploaded_by' => $admin->id,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($admin)->delete(route('projects.documents.destroy', [$project, $document]));

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Documento eliminado correctamente.');

        $this->assertDatabaseMissing('project_documents', ['id' => $document->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_unauthorized_user_cannot_manage_documents(): void
    {
        $admin = User::first();
        $unauthorized = User::factory()->create();
        $unauthorized->assignRole('operations');

        $project = Project::create([
            'title' => 'In Execution Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $file = UploadedFile::fake()->create('contract.pdf', 500);

        $response = $this->actingAs($unauthorized)->post(route('projects.documents.store', $project), [
            'document' => $file,
        ]);
        $response->assertStatus(403);

        $path = $file->store("project-documents/{$project->id}", 'public');
        $document = $project->documents()->create([
            'original_name' => 'contract.pdf',
            'file_path' => $path,
            'file_type' => 'pdf',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($unauthorized)->delete(route('projects.documents.destroy', [$project, $document]));
        $response->assertStatus(403);
    }

    public function test_document_management_blocked_when_project_not_in_progress(): void
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

        $file = UploadedFile::fake()->create('contract.pdf', 500);

        $response = $this->actingAs($admin)->post(route('projects.documents.store', $project), [
            'document' => $file,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'Solo se pueden gestionar documentos en proyectos en ejecución.');

        $path = $file->store("project-documents/{$project->id}", 'public');
        $document = $project->documents()->create([
            'original_name' => 'contract.pdf',
            'file_path' => $path,
            'file_type' => 'pdf',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('projects.documents.destroy', [$project, $document]));

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'Solo se pueden gestionar documentos en proyectos en ejecución.');

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertDontSee('Adjuntar Documento');
        $response->assertDontSee('Eliminar');
    }

    public function test_documents_are_displayed_on_project_show(): void
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
            'original_name' => 'report_a.pdf',
            'file_path' => 'project-documents/1/report_a.pdf',
            'file_type' => 'pdf',
            'uploaded_by' => $admin->id,
        ]);

        $project->documents()->create([
            'original_name' => 'invoice.xlsx',
            'file_path' => 'project-documents/1/invoice.xlsx',
            'file_type' => 'xlsx',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertSee('Documentos del Proyecto');
        $response->assertSee('report_a.pdf');
        $response->assertSee('PDF');
        $response->assertSee('invoice.xlsx');
        $response->assertSee('XLSX');
        $response->assertSee('Subido por:');
    }

    public function test_document_must_belong_to_project_to_be_deleted(): void
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

        $document = $project1->documents()->create([
            'original_name' => 'doc1.pdf',
            'file_path' => 'project-documents/1/doc1.pdf',
            'file_type' => 'pdf',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('projects.documents.destroy', [$project2, $document]));
        $response->assertStatus(404);
    }

    public function test_document_button_visibility_on_project_show(): void
    {
        $admin = User::first();
        $unauthorized = User::factory()->create();
        $unauthorized->assignRole('operations');

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

        $response = $this->actingAs($admin)->get(route('projects.show', $projectExecution));
        $response->assertSee('Adjuntar Documento');

        $response = $this->actingAs($admin)->get(route('projects.show', $projectPending));
        $response->assertDontSee('Adjuntar Documento');

        $response = $this->actingAs($unauthorized)->get(route('projects.show', $projectExecution));
        $response->assertDontSee('Adjuntar Documento');
    }
}