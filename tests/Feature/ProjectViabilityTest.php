<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectViabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
    }

    private function createProject(): Project
    {
        $admin = User::first();
        return Project::create([
            'title' => 'Test Project',
            'description' => 'Test Description',
            'criticality' => 'high',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);
    }

    public function test_viability_dictamen_calculated_with_multiple_evaluations(): void
    {
        $project = $this->createProject();

        $evaluator1 = User::factory()->create();
        $evaluator1->assignRole('junta');

        $evaluator2 = User::factory()->create();
        $evaluator2->assignRole('junta');

        ProjectEvaluation::create([
            'project_id' => $project->id,
            'user_id' => $evaluator1->id,
            'technical_score' => 8,
            'financial_score' => 8,
            'operational_score' => 8,
            'regulatory_score' => 8,
        ]);

        ProjectEvaluation::create([
            'project_id' => $project->id,
            'user_id' => $evaluator2->id,
            'technical_score' => 6,
            'financial_score' => 6,
            'operational_score' => 6,
            'regulatory_score' => 6,
        ]);

        $project->refresh();

        $this->assertEquals(7.00, $project->average_viability_score);
        $this->assertEquals('viable', $project->viability_label);
    }

    public function test_viability_dictamen_conditional(): void
    {
        $project = $this->createProject();

        $evaluator = User::factory()->create();
        $evaluator->assignRole('junta');

        ProjectEvaluation::create([
            'project_id' => $project->id,
            'user_id' => $evaluator->id,
            'technical_score' => 5,
            'financial_score' => 5,
            'operational_score' => 5,
            'regulatory_score' => 5,
        ]);

        $project->refresh();

        $this->assertEquals(5.00, $project->average_viability_score);
        $this->assertEquals('conditional', $project->viability_label);
    }

    public function test_viability_dictamen_not_viable(): void
    {
        $project = $this->createProject();

        $evaluator = User::factory()->create();
        $evaluator->assignRole('junta');

        ProjectEvaluation::create([
            'project_id' => $project->id,
            'user_id' => $evaluator->id,
            'technical_score' => 3,
            'financial_score' => 3,
            'operational_score' => 3,
            'regulatory_score' => 3,
        ]);

        $project->refresh();

        $this->assertEquals(3.00, $project->average_viability_score);
        $this->assertEquals('not_viable', $project->viability_label);
    }

    public function test_project_show_page_displays_viability_dictamen(): void
    {
        $project = $this->createProject();
        $evaluator = User::factory()->create();
        $evaluator->assignRole('junta');

        ProjectEvaluation::create([
            'project_id' => $project->id,
            'user_id' => $evaluator->id,
            'technical_score' => 9,
            'financial_score' => 9,
            'operational_score' => 9,
            'regulatory_score' => 9,
        ]);

        $admin = User::first();

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertSee('Dictamen General de Viabilidad');
        $response->assertSee('Viable');
        $response->assertSee('9.00/10');
    }

    public function test_project_show_page_without_evaluations_displays_error(): void
    {
        $project = $this->createProject();
        $admin = User::first();

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertSee('No es posible generar un dictamen de viabilidad sin evaluaciones.');
    }
}
