<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
        $this->withoutVite();
    }

    public function test_authorized_user_can_update_status(): void
    {
        $admin = User::first();
        $owner = User::factory()->create();
        $owner->assignRole('administration');

        $project = Project::create([
            'title' => 'Test Project',
            'description' => 'Test description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $owner->id,
            'status' => 'pending',
        ]);

        $statuses = ['en_analisis', 'prioritized', 'in_progress', 'paused', 'closed'];

        foreach ($statuses as $status) {
            $response = $this->actingAs($admin)->patch(route('projects.status.update', $project), [
                'status' => $status,
            ]);

            $response->assertRedirect(route('projects.show', $project));
            $response->assertSessionHas('success', 'Estado del proyecto actualizado exitosamente.');
            $this->assertEquals($status, $project->fresh()->status);

            if ($status !== 'closed') {
                $project->update(['status' => 'pending']);
            }
        }
    }

    public function test_unauthorized_user_cannot_update_status(): void
    {
        $admin = User::first();
        $owner = User::factory()->create();
        $owner->assignRole('administration');

        $project = Project::create([
            'title' => 'Test Project',
            'description' => 'Test description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $owner->id,
            'status' => 'pending',
        ]);

        $otherUser = User::factory()->create();
        $otherUser->assignRole('operations');

        $response = $this->actingAs($otherUser)->patch(route('projects.status.update', $project), [
            'status' => 'en_analisis',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $project->fresh()->status);
    }

    public function test_cannot_update_status_for_closed_project(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Closed Project',
            'description' => 'Closed description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($admin)->patch(route('projects.status.update', $project), [
            'status' => 'pending',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'No se puede cambiar el estado de un proyecto finalizado.');
        $this->assertEquals('closed', $project->fresh()->status);
    }

    public function test_closed_project_does_not_display_status_change_form(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Closed Project',
            'description' => 'Closed description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertDontSee('Cambiar Estado del Proyecto');
        $response->assertSee('Finalizado');
    }

    public function test_rejected_project_displays_status_but_does_not_show_rejected_as_selectable_status(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Rejected Project',
            'description' => 'Rejected description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertSee('Cambiar Estado del Proyecto');
        $response->assertDontSee('value="rejected"');
        $response->assertSee('Rechazado');
    }

    public function test_validation_rejects_invalid_status(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Test Project',
            'description' => 'Test description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('projects.status.update', $project), [
            'status' => 'invalid_status_value',
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertEquals('pending', $project->fresh()->status);

        $response = $this->actingAs($admin)->patch(route('projects.status.update', $project), [
            'status' => 'rejected',
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertEquals('pending', $project->fresh()->status);
    }

    public function test_approved_status_requires_estimated_cost(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Test Project',
            'description' => 'Test description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('projects.status.update', $project), [
            'status' => 'approved',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'No se puede aprobar el proyecto porque no tiene presupuesto disponible.');
        $this->assertEquals('pending', $project->fresh()->status);

        $project->update(['estimated_cost' => 5000]);

        $response = $this->actingAs($admin)->patch(route('projects.status.update', $project), [
            'status' => 'approved',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Estado del proyecto actualizado exitosamente.');
        $this->assertEquals('approved', $project->fresh()->status);
    }
}
