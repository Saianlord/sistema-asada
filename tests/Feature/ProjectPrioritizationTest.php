<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPrioritizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
    }

    private function createProject(string $title): Project
    {
        $admin = User::first();
        return Project::create([
            'title' => $title,
            'description' => 'Test Description',
            'criticality' => 'medium',
            'priority' => 'medium',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);
    }

    public function test_junta_and_admin_users_can_access_prioritization_page(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->get(route('projects.prioritization'));
        $response->assertStatus(200);

        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');
        $response = $this->actingAs($juntaUser)->get(route('projects.prioritization'));
        $response->assertStatus(200);
    }

    public function test_unauthorized_users_cannot_access_prioritization_page(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $response = $this->actingAs($operator)->get(route('projects.prioritization'));
        $response->assertStatus(403);
    }

    public function test_prioritization_shows_projects_sorted_by_viability_score_descending(): void
    {
        $projectLow = $this->createProject('Low Viability Project');
        $projectHigh = $this->createProject('High Viability Project');
        $projectMedium = $this->createProject('Medium Viability Project');

        $evaluator = User::factory()->create();
        $evaluator->assignRole('junta');

        ProjectEvaluation::create([
            'project_id' => $projectLow->id,
            'user_id' => $evaluator->id,
            'technical_score' => 2,
            'financial_score' => 3,
            'operational_score' => 2,
            'regulatory_score' => 3,
        ]);

        ProjectEvaluation::create([
            'project_id' => $projectHigh->id,
            'user_id' => $evaluator->id,
            'technical_score' => 9,
            'financial_score' => 9,
            'operational_score' => 9,
            'regulatory_score' => 10,
        ]);

        ProjectEvaluation::create([
            'project_id' => $projectMedium->id,
            'user_id' => $evaluator->id,
            'technical_score' => 6,
            'financial_score' => 6,
            'operational_score' => 6,
            'regulatory_score' => 6,
        ]);

        $admin = User::first();
        $response = $this->actingAs($admin)->get(route('projects.prioritization'));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            'High Viability Project',
            'Medium Viability Project',
            'Low Viability Project',
        ]);
    }

    public function test_prioritization_shows_empty_message_when_no_projects_have_evaluations(): void
    {
        $this->createProject('Project Without Evaluations');

        $admin = User::first();
        $response = $this->actingAs($admin)->get(route('projects.prioritization'));

        $response->assertStatus(200);
        $response->assertSee('No hay datos suficientes para generar la priorización');
        $response->assertDontSee('Project Without Evaluations');
    }

    public function test_prioritization_updates_dynamically_when_evaluations_are_added(): void
    {
        $projectA = $this->createProject('Project A');
        $projectB = $this->createProject('Project B');

        $evaluator = User::factory()->create();
        $evaluator->assignRole('junta');

        ProjectEvaluation::create([
            'project_id' => $projectA->id,
            'user_id' => $evaluator->id,
            'technical_score' => 5,
            'financial_score' => 5,
            'operational_score' => 5,
            'regulatory_score' => 5,
        ]);

        ProjectEvaluation::create([
            'project_id' => $projectB->id,
            'user_id' => $evaluator->id,
            'technical_score' => 6,
            'financial_score' => 6,
            'operational_score' => 6,
            'regulatory_score' => 6,
        ]);

        $admin = User::first();
        $response = $this->actingAs($admin)->get(route('projects.prioritization'));
        $response->assertSeeInOrder([
            'Project B',
            'Project A',
        ]);

        ProjectEvaluation::create([
            'project_id' => $projectA->id,
            'user_id' => $admin->id,
            'technical_score' => 10,
            'financial_score' => 10,
            'operational_score' => 10,
            'regulatory_score' => 10,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.prioritization'));
        $response->assertSeeInOrder([
            'Project A',
            'Project B',
        ]);
    }
}
