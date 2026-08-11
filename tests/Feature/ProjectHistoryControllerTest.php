<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
    }

    public function test_authorized_user_can_view_project_history()
    {
        $user = User::factory()->create();
        $user->assignRole('fiscal');
        
        $project = Project::create([
            'title' => 'Project History Test',
            'description' => 'Test Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('projects.history.index', $project));

        $response->assertStatus(200);
        $response->assertViewIs('projects.history');
        $response->assertSee('Historial del Proyecto');
    }

    public function test_unauthorized_user_cannot_view_project_history()
    {
        $projectUser = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $project = Project::create([
            'title' => 'Project Unauth Test',
            'description' => 'Test Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $projectUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($otherUser)->get(route('projects.history.index', $project));

        $response->assertStatus(403);
    }
    
    public function test_history_shows_empty_state_when_no_logs_exist()
    {
        $user = User::first(); // admin from seeder
        
        $project = Project::create([
            'title' => 'Project Empty History Test',
            'description' => 'Test Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        
        // Clear automatic creation logs for the test
        $project->auditLogs()->delete();

        $response = $this->actingAs($user)->get(route('projects.history.index', $project));

        $response->assertStatus(200);
        $response->assertSee('No hay informaci');
    }
}

