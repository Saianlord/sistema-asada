<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectReportTest extends TestCase
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
        ];

        return Project::create(array_merge($defaults, $overrides));
    }

    public function test_junta_can_view_project_report_with_filters(): void
    {
        $this->createProject(['title' => 'Proyecto A', 'status' => 'approved', 'criticality' => 'high', 'priority' => 'high']);
        $this->createProject(['title' => 'Proyecto B', 'status' => 'pending', 'criticality' => 'medium', 'priority' => 'low']);
        $this->createProject(['title' => 'Proyecto C', 'status' => 'approved', 'criticality' => 'low', 'priority' => 'medium']);

        $junta = User::factory()->create();
        $junta->assignRole('junta');

        $response = $this->actingAs($junta)->get(route('projects.reports.index', ['status' => 'approved', 'priority' => 'high']));

        $response->assertStatus(200);
        $response->assertSee('Proyecto A');
        $response->assertDontSee('Proyecto B');
        $response->assertDontSee('Proyecto C');
        $response->assertSee('Reporte de Proyectos');
    }

    public function test_unauthorized_users_cannot_access_report_page(): void
    {
        $operations = User::factory()->create();
        $operations->assignRole('operations');

        $response = $this->actingAs($operations)->get(route('projects.reports.index'));

        $response->assertStatus(403);
    }
}
