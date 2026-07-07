<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectTracking;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
        $this->withoutVite();
    }

    public function test_authorized_user_can_access_tracking_create_page(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.tracking.create', $project));
        $response->assertStatus(200);
        $response->assertSee('Registrar Seguimiento');
    }

    public function test_management_blocked_when_project_not_in_execution(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Pending Project',
            'description' => 'Pending description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $tracking = $project->trackings()->create([
            'type' => 'milestone',
            'title' => 'Initial Milestone',
            'description' => 'Milestone Description',
            'date' => '2026-07-10',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.tracking.create', $project));
        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'Solo se puede registrar o actualizar el seguimiento de proyectos en ejecución.');

        $response = $this->actingAs($admin)->post(route('projects.tracking.store', $project), [
            'type' => 'milestone',
            'title' => 'New Milestone',
            'description' => 'Description',
            'date' => '2026-07-10',
        ]);
        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'Solo se puede registrar o actualizar el seguimiento de proyectos en ejecución.');

        $response = $this->actingAs($admin)->get(route('projects.tracking.edit', [$project, $tracking]));
        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'Solo se puede registrar o actualizar el seguimiento de proyectos en ejecución.');

        $response = $this->actingAs($admin)->put(route('projects.tracking.update', [$project, $tracking]), [
            'type' => 'milestone',
            'title' => 'Updated Milestone',
            'description' => 'Updated description',
            'date' => '2026-07-15',
        ]);
        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'Solo se puede registrar o actualizar el seguimiento de proyectos en ejecución.');
    }

    public function test_authorized_user_can_create_tracking_record(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $types = ['milestone', 'progress', 'incident'];

        foreach ($types as $type) {
            $response = $this->actingAs($admin)->post(route('projects.tracking.store', $project), [
                'type' => $type,
                'title' => "Title {$type}",
                'description' => "Description {$type}",
                'date' => '2026-07-10',
            ]);

            $response->assertRedirect(route('projects.show', $project));
            $response->assertSessionHas('success', 'Registro de seguimiento creado exitosamente.');

            $this->assertDatabaseHas('project_trackings', [
                'project_id' => $project->id,
                'type' => $type,
                'title' => "Title {$type}",
                'description' => "Description {$type}",
                'date' => '2026-07-10',
            ]);
        }
    }

    public function test_create_tracking_validation_errors(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->post(route('projects.tracking.store', $project), [
            'type' => 'invalid_type',
            'title' => '',
            'description' => '',
            'date' => 'not-a-date',
        ]);

        $response->assertSessionHasErrors(['type', 'title', 'description', 'date']);
        $this->assertDatabaseEmpty('project_trackings');
    }

    public function test_authorized_user_can_update_tracking_record(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $tracking = $project->trackings()->create([
            'type' => 'milestone',
            'title' => 'Old Title',
            'description' => 'Old description',
            'date' => '2026-07-10',
        ]);

        $response = $this->actingAs($admin)->put(route('projects.tracking.update', [$project, $tracking]), [
            'type' => 'progress',
            'title' => 'New Title',
            'description' => 'New description',
            'date' => '2026-07-20',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Registro de seguimiento actualizado exitosamente.');

        $this->assertDatabaseHas('project_trackings', [
            'id' => $tracking->id,
            'type' => 'progress',
            'title' => 'New Title',
            'description' => 'New description',
            'date' => '2026-07-20',
        ]);
    }

    public function test_unauthorized_user_cannot_manage_tracking_records(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $unauthorized = User::factory()->create();
        $unauthorized->assignRole('operations');

        $response = $this->actingAs($unauthorized)->get(route('projects.tracking.create', $project));
        $response->assertStatus(403);

        $response = $this->actingAs($unauthorized)->post(route('projects.tracking.store', $project), [
            'type' => 'milestone',
            'title' => 'Hito',
            'description' => 'Desc',
            'date' => '2026-07-10',
        ]);
        $response->assertStatus(403);
    }

    public function test_tracking_records_displayed_correctly_on_project_show(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $project->trackings()->create([
            'type' => 'milestone',
            'title' => 'Hito Important',
            'description' => 'Description Milestone',
            'date' => '2026-07-10',
        ]);

        $project->trackings()->create([
            'type' => 'incident',
            'title' => 'Incidencia Critica',
            'description' => 'Description Incident',
            'date' => '2026-07-11',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertSee('Seguimiento del Proyecto');
        $response->assertSee('Hito Important');
        $response->assertSee('Hito');
        $response->assertSee('Incidencia Critica');
        $response->assertSee('Incidencia');
    }

    public function test_tracking_record_must_belong_to_project_to_be_managed(): void
    {
        $admin = User::first();

        $project1 = Project::create([
            'title' => 'Project 1',
            'description' => 'Desc 1',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $project2 = Project::create([
            'title' => 'Project 2',
            'description' => 'Desc 2',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $tracking = $project1->trackings()->create([
            'type' => 'milestone',
            'title' => 'Milestone belonging to Project 1',
            'description' => 'Description 1',
            'date' => '2026-07-10',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.tracking.edit', [$project2, $tracking]));
        $response->assertStatus(404);

        $response = $this->actingAs($admin)->put(route('projects.tracking.update', [$project2, $tracking]), [
            'type' => 'milestone',
            'title' => 'New Title',
            'description' => 'New description',
            'date' => '2026-07-20',
        ]);
        $response->assertStatus(404);
    }

    public function test_registrar_seguimiento_button_visibility_in_project_show(): void
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
        $response->assertSee('Registrar Seguimiento');

        $response = $this->actingAs($admin)->get(route('projects.show', $projectPending));
        $response->assertDontSee('Registrar Seguimiento');

        $response = $this->actingAs($unauthorized)->get(route('projects.show', $projectExecution));
        $response->assertDontSee('Registrar Seguimiento');
    }
}