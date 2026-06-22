<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\User;
use App\Models\ViabilityModelConfiguration;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViabilityModelConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
        $this->withoutVite();
        ViabilityModelConfiguration::clearCache();
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

    public function test_guest_is_redirected_from_viability_config_page(): void
    {
        $response = $this->get(route('viability-config.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_roles_cannot_access_viability_config_page(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $response = $this->actingAs($operator)->get(route('viability-config.edit'));
        $response->assertStatus(403);

        $responseUpdate = $this->actingAs($operator)->put(route('viability-config.update'), [
            'technical_weight' => 25,
            'financial_weight' => 25,
            'operational_weight' => 25,
            'regulatory_weight' => 25,
            'viable_threshold' => 7.00,
            'conditional_threshold' => 4.00,
        ]);
        $responseUpdate->assertStatus(403);
    }

    public function test_admin_and_junta_can_access_viability_config_page(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->get(route('viability-config.edit'));
        $response->assertStatus(200);

        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');
        $responseJunta = $this->actingAs($juntaUser)->get(route('viability-config.edit'));
        $responseJunta->assertStatus(200);
    }

    public function test_validation_fails_when_weights_do_not_sum_to_100(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->put(route('viability-config.update'), [
            'technical_weight' => 30,
            'financial_weight' => 20,
            'operational_weight' => 20,
            'regulatory_weight' => 20,
            'viable_threshold' => 7.00,
            'conditional_threshold' => 4.00,
        ]);

        $response->assertSessionHasErrors(['technical_weight']);
    }

    public function test_validation_fails_when_viable_threshold_is_less_than_or_equal_to_conditional(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->put(route('viability-config.update'), [
            'technical_weight' => 25,
            'financial_weight' => 25,
            'operational_weight' => 25,
            'regulatory_weight' => 25,
            'viable_threshold' => 4.00,
            'conditional_threshold' => 5.00,
        ]);

        $response->assertSessionHasErrors(['viable_threshold']);
    }

    public function test_successful_config_update(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->put(route('viability-config.update'), [
            'technical_weight' => 40,
            'financial_weight' => 20,
            'operational_weight' => 20,
            'regulatory_weight' => 20,
            'viable_threshold' => 8.00,
            'conditional_threshold' => 5.00,
        ]);

        $response->assertRedirect(route('viability-config.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('viability_model_configurations', [
            'technical_weight' => 40,
            'financial_weight' => 20,
            'operational_weight' => 20,
            'regulatory_weight' => 20,
            'viable_threshold' => 8.00,
            'conditional_threshold' => 5.00,
        ]);
    }

    public function test_updating_config_recalculates_existing_evaluations(): void
    {
        $project = $this->createProject();
        $evaluator = User::factory()->create();
        $evaluator->assignRole('junta');

        $evaluation = ProjectEvaluation::create([
            'project_id' => $project->id,
            'user_id' => $evaluator->id,
            'technical_score' => 10,
            'financial_score' => 2,
            'operational_score' => 2,
            'regulatory_score' => 2,
        ]);

        $this->assertEquals(4.00, $evaluation->average_score);
        $this->assertEquals('conditional', $evaluation->viability_status);

        $admin = User::first();
        $this->actingAs($admin)->put(route('viability-config.update'), [
            'technical_weight' => 70,
            'financial_weight' => 10,
            'operational_weight' => 10,
            'regulatory_weight' => 10,
            'viable_threshold' => 8.00,
            'conditional_threshold' => 6.00,
        ]);

        $evaluation->refresh();
        $this->assertEquals(7.60, $evaluation->average_score);
        $this->assertEquals('conditional', $evaluation->viability_status);

        $this->actingAs($admin)->put(route('viability-config.update'), [
            'technical_weight' => 70,
            'financial_weight' => 10,
            'operational_weight' => 10,
            'regulatory_weight' => 10,
            'viable_threshold' => 7.50,
            'conditional_threshold' => 6.00,
        ]);

        $evaluation->refresh();
        $this->assertEquals('viable', $evaluation->viability_status);
    }
}
