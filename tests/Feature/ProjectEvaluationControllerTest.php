<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectEvaluationControllerTest extends TestCase
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

    public function test_junta_user_can_access_evaluation_form(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        $response = $this->actingAs($juntaUser)->get(route('evaluations.create', $project));
        $response->assertStatus(200);
        $response->assertSee('Evaluar Iniciativa');
    }

    public function test_admin_can_access_evaluation_form(): void
    {
        $project = $this->createProject();
        $admin = User::first();

        $response = $this->actingAs($admin)->get(route('evaluations.create', $project));
        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_access_evaluation_form(): void
    {
        $project = $this->createProject();
        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $response = $this->actingAs($operator)->get(route('evaluations.create', $project));
        $response->assertStatus(403);
    }

    public function test_successful_evaluation_submission(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        $response = $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
            'technical_score' => 8,
            'financial_score' => 7,
            'operational_score' => 9,
            'regulatory_score' => 6,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Evaluación registrada exitosamente.');

        $this->assertDatabaseHas('project_evaluations', [
            'project_id' => $project->id,
            'user_id' => $juntaUser->id,
            'technical_score' => 8,
            'financial_score' => 7,
            'operational_score' => 9,
            'regulatory_score' => 6,
        ]);
    }

    public function test_evaluation_calculates_average_and_viability(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
            'technical_score' => 8,
            'financial_score' => 8,
            'operational_score' => 8,
            'regulatory_score' => 8,
        ]);

        $evaluation = ProjectEvaluation::first();
        $this->assertEquals(8.00, $evaluation->average_score);
        $this->assertEquals('viable', $evaluation->viability_status);
    }

    public function test_evaluation_conditional_viability(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
            'technical_score' => 5,
            'financial_score' => 5,
            'operational_score' => 5,
            'regulatory_score' => 5,
        ]);

        $evaluation = ProjectEvaluation::first();
        $this->assertEquals(5.00, $evaluation->average_score);
        $this->assertEquals('conditional', $evaluation->viability_status);
    }

    public function test_evaluation_not_viable(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
            'technical_score' => 2,
            'financial_score' => 3,
            'operational_score' => 1,
            'regulatory_score' => 2,
        ]);

        $evaluation = ProjectEvaluation::first();
        $this->assertEquals(2.00, $evaluation->average_score);
        $this->assertEquals('not_viable', $evaluation->viability_status);
    }

    public function test_validation_errors_for_missing_fields(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        $response = $this->actingAs($juntaUser)->post(route('evaluations.store', $project), []);

        $response->assertSessionHasErrors([
            'technical_score',
            'financial_score',
            'operational_score',
            'regulatory_score',
        ]);
    }

    public function test_validation_errors_for_out_of_range_values(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        $response = $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
            'technical_score' => 0,
            'financial_score' => 11,
            'operational_score' => -1,
            'regulatory_score' => 100,
        ]);

        $response->assertSessionHasErrors([
            'technical_score',
            'financial_score',
            'operational_score',
            'regulatory_score',
        ]);

        $this->assertDatabaseMissing('project_evaluations', [
            'project_id' => $project->id,
        ]);
    }

    public function test_duplicate_evaluation_is_prevented(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
            'technical_score' => 8,
            'financial_score' => 7,
            'operational_score' => 9,
            'regulatory_score' => 6,
        ]);

        $response = $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
            'technical_score' => 5,
            'financial_score' => 5,
            'operational_score' => 5,
            'regulatory_score' => 5,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('info');

        $this->assertEquals(1, ProjectEvaluation::where('project_id', $project->id)->where('user_id', $juntaUser->id)->count());
    }

    public function test_user_can_update_their_own_evaluation(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
            'technical_score' => 5,
            'financial_score' => 5,
            'operational_score' => 5,
            'regulatory_score' => 5,
        ]);

        $evaluation = ProjectEvaluation::first();

        $response = $this->actingAs($juntaUser)->put(route('evaluations.update', [$project, $evaluation]), [
            'technical_score' => 9,
            'financial_score' => 8,
            'operational_score' => 7,
            'regulatory_score' => 10,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Evaluación actualizada exitosamente.');

        $evaluation->refresh();
        $this->assertEquals(9, $evaluation->technical_score);
        $this->assertEquals(8, $evaluation->financial_score);
        $this->assertEquals(7, $evaluation->operational_score);
        $this->assertEquals(10, $evaluation->regulatory_score);
    }

    public function test_user_cannot_edit_another_users_evaluation(): void
    {
        $project = $this->createProject();
        $juntaUser1 = User::factory()->create();
        $juntaUser1->assignRole('junta');
        $juntaUser2 = User::factory()->create();
        $juntaUser2->assignRole('junta');

        $this->actingAs($juntaUser1)->post(route('evaluations.store', $project), [
            'technical_score' => 5,
            'financial_score' => 5,
            'operational_score' => 5,
            'regulatory_score' => 5,
        ]);

        $evaluation = ProjectEvaluation::first();

        $response = $this->actingAs($juntaUser2)->get(route('evaluations.edit', [$project, $evaluation]));
        $response->assertStatus(403);
    }

    public function test_show_page_displays_evaluations(): void
    {
        $project = $this->createProject();
        $juntaUser = User::factory()->create(['name' => 'Evaluator Test']);
        $juntaUser->assignRole('junta');

        $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
            'technical_score' => 8,
            'financial_score' => 7,
            'operational_score' => 9,
            'regulatory_score' => 6,
        ]);

        $response = $this->actingAs($juntaUser)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertSee('Evaluator Test');
        $response->assertSee('Evaluaciones');
    }

    public function test_show_page_without_evaluations_shows_message(): void
    {
        $project = $this->createProject();
        $admin = User::first();

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertSee('Este proyecto aún no cuenta con evaluaciones registradas.');
        $response->assertSee('No es posible generar un dictamen de viabilidad sin evaluaciones.');
    }

    public function test_cannot_access_evaluation_form_if_project_is_not_pending(): void
    {
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        foreach (['approved', 'rejected', 'closed'] as $status) {
            $project = $this->createProject();
            $project->update(['status' => $status]);

            $response = $this->actingAs($juntaUser)->get(route('evaluations.create', $project));

            $response->assertRedirect(route('projects.show', $project));
            $response->assertSessionHas('error', 'No se puede registrar ni editar la evaluación de un proyecto que ya fue aprobado, rechazado o cerrado.');
        }
    }

    public function test_cannot_store_evaluation_if_project_is_not_pending(): void
    {
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        foreach (['approved', 'rejected', 'closed'] as $status) {
            $project = $this->createProject();
            $project->update(['status' => $status]);

            $response = $this->actingAs($juntaUser)->post(route('evaluations.store', $project), [
                'technical_score' => 8,
                'financial_score' => 7,
                'operational_score' => 9,
                'regulatory_score' => 6,
            ]);

            $response->assertRedirect(route('projects.show', $project));
            $response->assertSessionHas('error', 'No se puede registrar ni editar la evaluación de un proyecto que ya fue aprobado, rechazado o cerrado.');

            $this->assertDatabaseMissing('project_evaluations', [
                'project_id' => $project->id,
                'user_id' => $juntaUser->id,
            ]);
        }
    }

    public function test_cannot_edit_evaluation_if_project_is_not_pending(): void
    {
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        foreach (['approved', 'rejected', 'closed'] as $status) {
            $project = $this->createProject();

            $evaluation = ProjectEvaluation::create([
                'project_id' => $project->id,
                'user_id' => $juntaUser->id,
                'technical_score' => 5,
                'financial_score' => 5,
                'operational_score' => 5,
                'regulatory_score' => 5,
            ]);

            $project->update(['status' => $status]);

            $response = $this->actingAs($juntaUser)->get(route('evaluations.edit', [$project, $evaluation]));

            $response->assertRedirect(route('projects.show', $project));
            $response->assertSessionHas('error', 'No se puede registrar ni editar la evaluación de un proyecto que ya fue aprobado, rechazado o cerrado.');
        }
    }

    public function test_cannot_update_evaluation_if_project_is_not_pending(): void
    {
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        foreach (['approved', 'rejected', 'closed'] as $status) {
            $project = $this->createProject();

            $evaluation = ProjectEvaluation::create([
                'project_id' => $project->id,
                'user_id' => $juntaUser->id,
                'technical_score' => 5,
                'financial_score' => 5,
                'operational_score' => 5,
                'regulatory_score' => 5,
            ]);

            $project->update(['status' => $status]);

            $response = $this->actingAs($juntaUser)->put(route('evaluations.update', [$project, $evaluation]), [
                'technical_score' => 9,
                'financial_score' => 8,
                'operational_score' => 7,
                'regulatory_score' => 10,
            ]);

            $response->assertRedirect(route('projects.show', $project));
            $response->assertSessionHas('error', 'No se puede registrar ni editar la evaluación de un proyecto que ya fue aprobado, rechazado o cerrado.');

            $evaluation->refresh();
            $this->assertEquals(5, $evaluation->technical_score);
        }
    }

    public function test_show_page_does_not_display_evaluation_buttons_if_project_is_not_pending(): void
    {
        $juntaUser = User::factory()->create();
        $juntaUser->assignRole('junta');

        foreach (['approved', 'rejected', 'closed'] as $status) {
            $project = $this->createProject();
            $project->update(['status' => $status]);

            $response = $this->actingAs($juntaUser)->get(route('projects.show', $project));
            $response->assertStatus(200);
            $response->assertDontSee('Evaluar Proyecto');
            $response->assertDontSee('Editar Mi Evaluación');
        }
    }
}
