<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectSupportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
    }

    public function test_authorized_user_can_access_support_edit_page(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Project 1',
            'description' => 'Desc 1',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.support.edit', $project));
        $response->assertStatus(200);

        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $response = $this->actingAs($operator)->get(route('projects.support.edit', $project));
        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_access_support_edit_page(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Project 2',
            'description' => 'Desc 2',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
        ]);

        $staff = User::factory()->create();
        $staff->assignRole('administration');

        $response = $this->actingAs($staff)->get(route('projects.support.edit', $project));
        $response->assertStatus(403);
    }

    public function test_first_time_requires_evidence_file(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Project 3',
            'description' => 'Desc 3',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->put(route('projects.support.update', $project), [
            'technical_justification' => 'Some justification',
            'estimated_cost' => 1500.50,
            'impact' => 'High impact',
            'risk' => 'Low risk',
        ]);

        $response->assertSessionHasErrors(['evidence']);
    }

    public function test_successful_support_attachment_stores_file(): void
    {
        Storage::fake('public');

        $admin = User::first();
        $project = Project::create([
            'title' => 'Project 4',
            'description' => 'Desc 4',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
        ]);

        $file = UploadedFile::fake()->create('evidence.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->put(route('projects.support.update', $project), [
            'technical_justification' => 'Accurate technical justification',
            'estimated_cost' => 50000.00,
            'impact' => 'Social impact',
            'risk' => 'Geological risk',
            'evidence' => $file,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Información de respaldo guardada exitosamente.');

        $project->refresh();
        $this->assertEquals('Accurate technical justification', $project->technical_justification);
        $this->assertEquals(50000.00, $project->estimated_cost);
        $this->assertEquals('Social impact', $project->impact);
        $this->assertEquals('Geological risk', $project->risk);
        $this->assertNotNull($project->evidence_path);

        Storage::disk('public')->assertExists($project->evidence_path);
    }

    public function test_evidence_optional_during_update_and_invalid_filetype_shows_error(): void
    {
        Storage::fake('public');

        $admin = User::first();
        $project = Project::create([
            'title' => 'Project 5',
            'description' => 'Desc 5',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'technical_justification' => 'Old justification',
            'estimated_cost' => 100.00,
            'impact' => 'Old impact',
            'risk' => 'Old risk',
            'evidence_path' => 'projects/evidences/old.pdf',
        ]);

        $response = $this->actingAs($admin)->put(route('projects.support.update', $project), [
            'technical_justification' => 'New justification',
            'estimated_cost' => 200.00,
            'impact' => 'New impact',
            'risk' => 'New risk',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $project->refresh();
        $this->assertEquals('New justification', $project->technical_justification);
        $this->assertEquals('projects/evidences/old.pdf', $project->evidence_path);

        $invalidFile = UploadedFile::fake()->create('document.zip', 500, 'application/zip');
        $response = $this->actingAs($admin)->put(route('projects.support.update', $project), [
            'technical_justification' => 'New justification',
            'estimated_cost' => 200.00,
            'impact' => 'New impact',
            'risk' => 'New risk',
            'evidence' => $invalidFile,
        ]);
        $response->assertSessionHasErrors(['evidence']);
    }

    public function test_authenticated_user_can_download_project_evidence(): void
    {
        Storage::fake('public');

        $admin = User::first();
        $file = UploadedFile::fake()->create('evidence.pdf', 500, 'application/pdf');
        $path = Storage::disk('public')->putFile('projects/evidences', $file);

        $project = Project::create([
            'title' => 'Project 6',
            'description' => 'Desc 6',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'technical_justification' => 'Technical justification',
            'estimated_cost' => 500.00,
            'impact' => 'High',
            'risk' => 'Low',
            'evidence_path' => $path,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.evidence.download', $project));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_cannot_download_nonexistent_evidence(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Project 7',
            'description' => 'Desc 7',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.evidence.download', $project));
        $response->assertStatus(404);
    }
}
