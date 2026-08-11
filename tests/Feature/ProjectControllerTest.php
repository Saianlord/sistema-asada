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
        $this->withoutVite();
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

    public function test_filtering_by_status(): void
    {
        $admin = User::first();
        Project::create([
            'title' => 'Project Pending',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);
        Project::create([
            'title' => 'Project Approved',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.index', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('Project Approved');
        $response->assertDontSee('Project Pending');
    }

    public function test_filtering_by_criticality(): void
    {
        $admin = User::first();
        Project::create([
            'title' => 'Project High Crit',
            'description' => 'Desc',
            'criticality' => 'high',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);
        Project::create([
            'title' => 'Project Low Crit',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.index', ['criticality' => 'high']));

        $response->assertStatus(200);
        $response->assertSee('Project High Crit');
        $response->assertDontSee('Project Low Crit');
    }

    public function test_filtering_by_priority(): void
    {
        $admin = User::first();
        Project::create([
            'title' => 'Project High Prio',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);
        Project::create([
            'title' => 'Project Low Prio',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.index', ['priority' => 'high']));

        $response->assertStatus(200);
        $response->assertSee('Project High Prio');
        $response->assertDontSee('Project Low Prio');
    }

    public function test_combining_multiple_filters(): void
    {
        $admin = User::first();
        Project::create([
            'title' => 'Target Project',
            'description' => 'Desc',
            'criticality' => 'high',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);
        Project::create([
            'title' => 'Other Project',
            'description' => 'Desc',
            'criticality' => 'high',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.index', [
            'status' => 'approved',
            'criticality' => 'high',
            'priority' => 'high',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Target Project');
        $response->assertDontSee('Other Project');
    }

    public function test_no_results_shows_message(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->get(route('projects.index', ['status' => 'closed']));

        $response->assertStatus(200);
        $response->assertSee('No se encontraron proyectos que coincidan con los filtros seleccionados.');
    }

    public function test_authorized_user_can_access_edit_page_if_pending(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Project Pending Edit',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.edit', $project));
        $response->assertStatus(200);
        $response->assertSee('Project Pending Edit');
    }

    public function test_authorized_user_cannot_access_edit_page_if_approved_or_closed(): void
    {
        $admin = User::first();
        $projectApproved = Project::create([
            'title' => 'Project Approved Edit',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);
        $projectClosed = Project::create([
            'title' => 'Project Closed Edit',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.edit', $projectApproved));
        $response->assertRedirect(route('projects.show', $projectApproved));
        $response->assertSessionHas('error', 'No se puede editar un proyecto que ya ha sido aprobado, rechazado o cerrado.');

        $response = $this->actingAs($admin)->get(route('projects.edit', $projectClosed));
        $response->assertRedirect(route('projects.show', $projectClosed));
        $response->assertSessionHas('error', 'No se puede editar un proyecto que ya ha sido aprobado, rechazado o cerrado.');
    }

    public function test_authorized_user_can_update_project(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Old Title',
            'description' => 'Old description',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put(route('projects.update', $project), [
            'title' => 'New Title',
            'description' => 'New description',
            'criticality' => 'high',
            'priority' => 'medium',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Iniciativa de proyecto actualizada exitosamente.');

        $project->refresh();
        $this->assertEquals('New Title', $project->title);
        $this->assertEquals('New description', $project->description);
        $this->assertEquals('high', $project->criticality);
        $this->assertEquals('medium', $project->priority);
    }

    public function test_unauthorized_user_cannot_access_edit_or_update(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Project Edit Auth Check',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $response = $this->actingAs($operator)->get(route('projects.edit', $project));
        $response->assertStatus(403);

        $response = $this->actingAs($operator)->put(route('projects.update', $project), [
            'title' => 'Hacked Title',
            'description' => 'Hacked description',
            'criticality' => 'high',
            'priority' => 'high',
        ]);
        $response->assertStatus(403);
    }

    public function test_update_restricted_for_approved_or_closed_projects(): void
    {
        $admin = User::first();
        $projectApproved = Project::create([
            'title' => 'Approved Title',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->put(route('projects.update', $projectApproved), [
            'title' => 'Changed Title',
            'description' => 'Desc changed',
            'criticality' => 'high',
            'priority' => 'high',
        ]);

        $response->assertRedirect(route('projects.show', $projectApproved));
        $response->assertSessionHas('error', 'No se puede editar un proyecto que ya ha sido aprobado, rechazado o cerrado.');

        $projectApproved->refresh();
        $this->assertEquals('Approved Title', $projectApproved->title);
    }

    public function test_authorized_user_can_approve_pending_project(): void
    {
        $junta = User::factory()->create();
        $junta->assignRole('junta');

        $admin = User::first();
        // HU-13: project must have a valid estimated_cost to be approvable
        $project = Project::create([
            'title' => 'Pending Project To Approve',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
            'estimated_cost' => 50000,
        ]);

        $response = $this->actingAs($junta)->patch(route('projects.approve', $project));

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Proyecto aprobado exitosamente.');

        $project->refresh();
        $this->assertEquals('approved', $project->status);
    }

    public function test_cannot_approve_project_without_budget(): void
    {
        $junta = User::factory()->create();
        $junta->assignRole('junta');

        $admin = User::first();
        // HU-13 Escenario 3: project with no budget must be blocked from approval
        $project = Project::create([
            'title' => 'Project Without Budget',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
            'estimated_cost' => null,
        ]);

        $response = $this->actingAs($junta)->patch(route('projects.approve', $project));

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'No se puede aprobar el proyecto porque no tiene presupuesto disponible.');

        $project->refresh();
        $this->assertEquals('pending', $project->status);
    }

    public function test_authorized_user_can_reject_pending_project(): void
    {
        $junta = User::factory()->create();
        $junta->assignRole('junta');

        $admin = User::first();
        $project = Project::create([
            'title' => 'Pending Project To Reject',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($junta)->patch(route('projects.reject', $project));

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Proyecto rechazado exitosamente.');

        $project->refresh();
        $this->assertEquals('rejected', $project->status);
    }

    public function test_unauthorized_user_cannot_approve_or_reject_project(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $admin = User::first();
        $project = Project::create([
            'title' => 'Pending Project Security Check',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $responseApprove = $this->actingAs($operator)->patch(route('projects.approve', $project));
        $responseApprove->assertStatus(403);

        $responseReject = $this->actingAs($operator)->patch(route('projects.reject', $project));
        $responseReject->assertStatus(403);

        $project->refresh();
        $this->assertEquals('pending', $project->status);
    }

    public function test_cannot_approve_or_reject_non_pending_project(): void
    {
        $admin = User::first();
        $projectApproved = Project::create([
            'title' => 'Approved Project',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);

        $responseApprove = $this->actingAs($admin)->patch(route('projects.approve', $projectApproved));
        $responseApprove->assertRedirect(route('projects.show', $projectApproved));
        $responseApprove->assertSessionHas('error', 'Solo los proyectos en estado pendiente, en análisis o priorizados pueden ser aprobados.');

        $responseReject = $this->actingAs($admin)->patch(route('projects.reject', $projectApproved));
        $responseReject->assertRedirect(route('projects.show', $projectApproved));
        $responseReject->assertSessionHas('error', 'Solo los proyectos en estado pendiente, en análisis o priorizados pueden ser rechazados.');
    }

    public function test_rejected_project_cannot_be_edited_or_updated(): void
    {
        $admin = User::first();
        $projectRejected = Project::create([
            'title' => 'Rejected Project',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'rejected',
        ]);

        $responseEdit = $this->actingAs($admin)->get(route('projects.edit', $projectRejected));
        $responseEdit->assertRedirect(route('projects.show', $projectRejected));
        $responseEdit->assertSessionHas('error', 'No se puede editar un proyecto que ya ha sido aprobado, rechazado o cerrado.');

        $responseUpdate = $this->actingAs($admin)->put(route('projects.update', $projectRejected), [
            'title' => 'Updated Title',
            'description' => 'Desc',
            'criticality' => 'high',
            'priority' => 'high',
        ]);
        $responseUpdate->assertRedirect(route('projects.show', $projectRejected));
        $responseUpdate->assertSessionHas('error', 'No se puede editar un proyecto que ya ha sido aprobado, rechazado o cerrado.');

        $projectRejected->refresh();
        $this->assertEquals('Rejected Project', $projectRejected->title);
    }

    public function test_filtering_by_rejected_status(): void
    {
        $admin = User::first();
        $projectRejected = Project::create([
            'title' => 'Project Rejected Filter',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'rejected',
        ]);
        $projectPending = Project::create([
            'title' => 'Project Pending Filter',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.index', ['status' => 'rejected']));

        $response->assertStatus(200);
        $response->assertSee('Project Rejected Filter');
        $response->assertDontSee('Project Pending Filter');
    }

    // =========================================================================
    // HU-13: Alerta de presupuesto para proyectos
    // =========================================================================

    public function test_cannot_approve_project_with_zero_budget(): void
    {
        // HU-13 Escenario 3: estimated_cost = 0 also has no available budget
        $junta = User::factory()->create();
        $junta->assignRole('junta');

        $admin = User::first();
        $project = Project::create([
            'title' => 'Project With Zero Budget',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
            'estimated_cost' => 0,
        ]);

        $response = $this->actingAs($junta)->patch(route('projects.approve', $project));

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'No se puede aprobar el proyecto porque no tiene presupuesto disponible.');

        $project->refresh();
        $this->assertEquals('pending', $project->status);
    }

    public function test_can_approve_project_with_valid_budget(): void
    {
        // HU-13 Escenario 2: project with assigned budget proceeds without alert
        $junta = User::factory()->create();
        $junta->assignRole('junta');

        $admin = User::first();
        $project = Project::create([
            'title' => 'Project With Valid Budget',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
            'estimated_cost' => 100000,
        ]);

        $response = $this->actingAs($junta)->patch(route('projects.approve', $project));

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Proyecto aprobado exitosamente.');

        $project->refresh();
        $this->assertEquals('approved', $project->status);
    }

    // =========================================================================
    // HU-14: Trazabilidad (Acuerdo, fecha, responsable, justificación)
    // =========================================================================

    public function test_authorized_user_can_access_approval_form_for_approved_project(): void
    {
        // HU-14: only approved projects can have traceability data recorded
        $admin = User::first();
        $project = Project::create([
            'title' => 'Approved Project For Traceability',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.approval.create', $project));
        $response->assertStatus(200);
        $response->assertSee('Acuerdo');
        $response->assertSee('Responsable');
    }

    public function test_approval_form_redirects_for_non_approved_project(): void
    {
        // HU-14: cannot register traceability on a pending project
        $admin = User::first();
        $project = Project::create([
            'title' => 'Pending Project For Traceability Block',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.approval.create', $project));
        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'Solo se puede registrar el acuerdo en proyectos aprobados.');
    }

    public function test_authorized_user_can_store_approval_traceability(): void
    {
        // HU-14 Escenario 1: successfully save all traceability fields
        $admin = User::first();
        $project = Project::create([
            'title' => 'Approved Project Traceability Save',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('projects.approval.store', $project), [
            'approval_agreement' => 'Acuerdo-2026-001',
            'approval_date' => '2026-06-22',
            'approval_responsible' => 'Juan Pérez',
            'approval_justification' => 'El proyecto cumple todos los criterios de viabilidad técnica y financiera.',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success', 'Registro de aprobación guardado correctamente.');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'approval_agreement' => 'Acuerdo-2026-001',
            'approval_responsible' => 'Juan Pérez',
        ]);
    }

    public function test_store_approval_validation_errors_for_missing_fields(): void
    {
        // HU-14 Escenario 2: all fields are required
        $admin = User::first();
        $project = Project::create([
            'title' => 'Approved Project Validation Test',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('projects.approval.store', $project), []);

        $response->assertSessionHasErrors([
            'approval_agreement',
            'approval_date',
            'approval_responsible',
            'approval_justification',
        ]);
    }

    public function test_store_approval_validation_errors_for_invalid_date(): void
    {
        // HU-14 Escenario 3: invalid data must be rejected
        $admin = User::first();
        $project = Project::create([
            'title' => 'Approved Project Invalid Date Test',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('projects.approval.store', $project), [
            'approval_agreement' => 'Acuerdo-001',
            'approval_date' => 'not-a-date',
            'approval_responsible' => 'Juan Pérez',
            'approval_justification' => 'Justificación válida.',
        ]);

        $response->assertSessionHasErrors(['approval_date']);
    }

    public function test_store_approval_blocked_for_non_approved_project(): void
    {
        // HU-14: cannot POST traceability on a project that is not approved
        $admin = User::first();
        $project = Project::create([
            'title' => 'Pending Project Traceability Block POST',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('projects.approval.store', $project), [
            'approval_agreement' => 'Acuerdo-001',
            'approval_date' => '2026-06-22',
            'approval_responsible' => 'Juan Pérez',
            'approval_justification' => 'Justificación.',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'Solo se puede registrar el acuerdo en proyectos aprobados.');

        $project->refresh();
        $this->assertNull($project->approval_agreement);
    }

    public function test_traceability_data_is_visible_on_project_show(): void
    {
        // HU-14 Escenario 4: stored traceability data is displayed on project detail
        $admin = User::first();
        $project = Project::create([
            'title' => 'Project With Full Traceability',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
            'approval_agreement' => 'Acuerdo-2026-Final',
            'approval_date' => '2026-06-22',
            'approval_responsible' => 'María López',
            'approval_justification' => 'Proyecto aprobado por la Junta en sesión ordinaria.',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $project));

        $response->assertStatus(200);
        $response->assertSee('Acuerdo-2026-Final');
        $response->assertSee('María López');
        $response->assertSee('Proyecto aprobado por la Junta en sesión ordinaria.');
    }

    public function test_unauthorized_user_cannot_access_approval_form_or_store(): void
    {
        // HU-14: only admin/administration role can manage traceability
        $operator = User::factory()->create();
        $operator->assignRole('operations');

        $admin = User::first();
        $project = Project::create([
            'title' => 'Project For Traceability Auth Check',
            'description' => 'Desc',
            'criticality' => 'low',
            'priority' => 'low',
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);

        $responseGet = $this->actingAs($operator)->get(route('projects.approval.create', $project));
        $responseGet->assertStatus(403);

        $responsePost = $this->actingAs($operator)->post(route('projects.approval.store', $project), [
            'approval_agreement' => 'Acuerdo-001',
            'approval_date' => '2026-06-22',
            'approval_responsible' => 'Juan',
            'approval_justification' => 'Justificación.',
        ]);
        $responsePost->assertStatus(403);
    }
}
