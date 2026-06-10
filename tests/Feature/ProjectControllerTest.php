<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
    }

    public function test_authorized_user_can_access_create_project_page(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->get(route('projects.create'));
        $response->assertStatus(200);

        $staff = User::factory()->create();
        $staff->assignRole('administration');
        $response = $this->actingAs($staff)->get(route('projects.create'));
        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_access_create_project_page(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $response = $this->actingAs($operator)->get(route('projects.create'));
        $response->assertStatus(403);
    }

    public function test_authorized_user_can_store_project_initiative(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'title' => 'Project Alpha',
            'description' => 'Detailed description of Project Alpha',
            'criticality' => 'medium',
            'priority' => 'high',
        ]);

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('success', 'Iniciativa de proyecto registrada exitosamente.');

        $this->assertDatabaseHas('projects', [
            'title' => 'Project Alpha',
            'description' => 'Detailed description of Project Alpha',
            'criticality' => 'medium',
            'priority' => 'high',
            'status' => 'pending',
            'user_id' => $admin->id,
        ]);
    }

    public function test_unauthorized_user_cannot_store_project_initiative(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $response = $this->actingAs($operator)->post(route('projects.store'), [
            'title' => 'Project Beta',
            'description' => 'Detailed description of Project Beta',
            'criticality' => 'low',
            'priority' => 'low',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('projects', [
            'title' => 'Project Beta',
        ]);
    }

    public function test_store_validation_errors_for_required_fields(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->post(route('projects.store'), []);

        $response->assertSessionHasErrors(['title', 'description', 'criticality', 'priority']);
    }

    public function test_store_validation_errors_for_invalid_enum_values(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'title' => 'Project Gamma',
            'description' => 'Valid description',
            'criticality' => 'super-high',
            'priority' => 'none',
        ]);

        $response->assertSessionHasErrors(['criticality', 'priority']);
    }

    public function test_authenticated_user_can_view_project_details(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Project Delta',
            'description' => 'Delta description',
            'criticality' => 'high',
            'priority' => 'medium',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $response = $this->actingAs($operator)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertSee('Project Delta');
        $response->assertSee('Delta description');
    }
}
