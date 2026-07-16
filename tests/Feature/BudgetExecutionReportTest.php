<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetExecutionReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
    }

    private function createProject(array $overrides = []): Project
    {
        $owner = User::factory()->create();
        $owner->assignRole('administration');

        $defaults = [
            'title' => 'Proyecto de Prueba',
            'description' => 'Descripción de prueba',
            'status' => 'pending',
            'criticality' => 'medium',
            'priority' => 'medium',
            'user_id' => $owner->id,
            'estimated_cost' => 1000000,
            'technical_justification' => 'Justificación de prueba',
            'impact' => 'Impacto de prueba',
            'risk' => 'Riesgo de prueba',
        ];

        return Project::create(array_merge($defaults, $overrides));
    }

    public function test_admin_can_view_budget_execution_report_with_data(): void
    {
        $this->createProject(['title' => 'Proyecto A', 'estimated_cost' => 1200000]);
        $this->createProject(['title' => 'Proyecto B', 'estimated_cost' => 800000]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('projects.budget-report'));

        $response->assertStatus(200);
        $response->assertSee('Reporte de Ejecución Presupuestaria');
        $response->assertSee('Proyecto A');
        $response->assertSee('Proyecto B');
        $response->assertSee('1,200,000.00');
    }

    public function test_admin_sees_empty_state_when_no_budget_data_exists(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('projects.budget-report'));

        $response->assertStatus(200);
        $response->assertSee('No hay información disponible');
    }
}
