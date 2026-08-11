<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndAdminSeeder::class);
    }

    public function test_fiscal_user_can_view_project_history_with_records(): void
    {
        $admin = User::first();
        $fiscal = User::factory()->create();
        $fiscal->assignRole('fiscal');

        $project = Project::create([
            'title' => 'Project History',
            'description' => 'History description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        ProjectHistory::create([
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'action_type' => 'project_updated',
            'title' => 'Título actualizado',
            'description' => 'Se actualizó el título del proyecto.',
            'details' => 'Título anterior: Project History. Título nuevo: Project History Updated.',
        ]);

        $response = $this->actingAs($fiscal)->get(route('projects.history.index', $project));

        $response->assertStatus(200);
        $response->assertSee('Historial de cambios y aprobaciones');
        $response->assertSee('Título actualizado');
        $response->assertSee('Project History');
    }

    public function test_fiscal_user_can_view_project_history_detail(): void
    {
        $admin = User::first();
        $fiscal = User::factory()->create();
        $fiscal->assignRole('fiscal');

        $project = Project::create([
            'title' => 'Project History Detail',
            'description' => 'History detail description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $history = ProjectHistory::create([
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'action_type' => 'project_approved',
            'title' => 'Proyecto aprobado',
            'description' => 'La iniciativa fue aprobada por la junta técnica.',
            'details' => 'Estado cambiado de pendiente a aprobado.',
        ]);

        $response = $this->actingAs($fiscal)->get(route('projects.history.show', [$project, $history]));

        $response->assertStatus(200);
        $response->assertSee('Detalle del Historial');
        $response->assertSee('Proyecto aprobado');
        $response->assertSee('La iniciativa fue aprobada por la junta técnica.');
        $response->assertSee('Estado cambiado de pendiente a aprobado.');
    }

    public function test_fiscal_user_sees_empty_history_message_if_no_records(): void
    {
        $admin = User::first();
        $fiscal = User::factory()->create();
        $fiscal->assignRole('fiscal');

        $project = Project::create([
            'title' => 'Empty History Project',
            'description' => 'No history content',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($fiscal)->get(route('projects.history.index', $project));

        $response->assertStatus(200);
        $response->assertSee('No hay información disponible para este proyecto.');
    }

    public function test_unauthorized_user_cannot_access_project_history(): void
    {
        $admin = User::first();
        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $project = Project::create([
            'title' => 'Project Denied',
            'description' => 'Should be denied',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($operator)->get(route('projects.history.index', $project));
        $response->assertStatus(403);
    }
}
