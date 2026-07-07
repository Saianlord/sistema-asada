<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectKanbanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
        $this->withoutVite();
    }

    public function test_authorized_user_can_access_kanban_board(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.kanban.index', $project));
        $response->assertStatus(200);
        $response->assertSee('Tablero Kanban');
    }

    public function test_access_blocked_when_project_not_in_progress(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Pending Project',
            'description' => 'Pending description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.kanban.index', $project));
        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('error', 'Solo se puede acceder al tablero Kanban de proyectos en ejecución.');
    }

    public function test_authorized_user_can_create_task(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $assignee = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('projects.tasks.store', $project), [
            'title' => 'First Task',
            'description' => 'Task description',
            'due_date' => '2026-08-01',
            'assigned_user_id' => $assignee->id,
        ]);

        $response->assertRedirect(route('projects.kanban.index', $project));
        $response->assertSessionHas('success', 'Tarea creada exitosamente.');

        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $project->id,
            'title' => 'First Task',
            'description' => 'Task description',
            'due_date' => '2026-08-01',
            'assigned_user_id' => $assignee->id,
            'status' => 'pending',
        ]);
    }

    public function test_create_task_validation_errors(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->post(route('projects.tasks.store', $project), [
            'title' => '',
            'due_date' => 'not-a-date',
            'assigned_user_id' => 9999,
        ]);

        $response->assertSessionHasErrors(['title', 'due_date', 'assigned_user_id']);
        $this->assertDatabaseEmpty('project_tasks');
    }

    public function test_authorized_user_can_update_task(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $assignee = User::factory()->create(['is_active' => true]);
        $task = $project->tasks()->create([
            'title' => 'Old Title',
            'description' => 'Old description',
            'due_date' => '2026-07-10',
            'assigned_user_id' => $assignee->id,
            'status' => 'pending',
        ]);

        $newAssignee = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->put(route('projects.tasks.update', [$project, $task]), [
            'title' => 'New Title',
            'description' => 'New description',
            'due_date' => '2026-07-20',
            'assigned_user_id' => $newAssignee->id,
            'status' => 'in_progress',
        ]);

        $response->assertRedirect(route('projects.kanban.index', $project));
        $response->assertSessionHas('success', 'Tarea actualizada exitosamente.');

        $this->assertDatabaseHas('project_tasks', [
            'id' => $task->id,
            'title' => 'New Title',
            'description' => 'New description',
            'due_date' => '2026-07-20',
            'assigned_user_id' => $newAssignee->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_authorized_user_can_change_task_status(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $assignee = User::factory()->create(['is_active' => true]);
        $task = $project->tasks()->create([
            'title' => 'Status Task',
            'due_date' => '2026-07-10',
            'assigned_user_id' => $assignee->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('projects.tasks.status.update', [$project, $task]), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect(route('projects.kanban.index', $project));
        $this->assertEquals('in_progress', $task->fresh()->status);
    }

    public function test_unauthorized_user_cannot_manage_tasks(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $assignee = User::factory()->create(['is_active' => true]);
        $task = $project->tasks()->create([
            'title' => 'Target Task',
            'due_date' => '2026-07-10',
            'assigned_user_id' => $assignee->id,
            'status' => 'pending',
        ]);

        $unauthorized = User::factory()->create();
        $unauthorized->assignRole('operations');

        $response = $this->actingAs($unauthorized)->post(route('projects.tasks.store', $project), [
            'title' => 'Forbidden Task',
            'due_date' => '2026-07-10',
            'assigned_user_id' => $assignee->id,
        ]);
        $response->assertStatus(403);

        $response = $this->actingAs($unauthorized)->patch(route('projects.tasks.status.update', [$project, $task]), [
            'status' => 'completed',
        ]);
        $response->assertStatus(403);
    }

    public function test_kanban_groups_tasks_by_status(): void
    {
        $admin = User::first();
        $project = Project::create([
            'title' => 'Execution Project',
            'description' => 'Execution description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $assignee = User::factory()->create(['is_active' => true]);

        $taskPending = $project->tasks()->create([
            'title' => 'Pending Task Title',
            'due_date' => '2026-07-10',
            'assigned_user_id' => $assignee->id,
            'status' => 'pending',
        ]);

        $taskInProgress = $project->tasks()->create([
            'title' => 'InProgress Task Title',
            'due_date' => '2026-07-10',
            'assigned_user_id' => $assignee->id,
            'status' => 'in_progress',
        ]);

        $taskCompleted = $project->tasks()->create([
            'title' => 'Completed Task Title',
            'due_date' => '2026-07-10',
            'assigned_user_id' => $assignee->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.kanban.index', $project));
        $response->assertStatus(200);

        $html = $response->getContent();

        $parts = explode('data-testid="kanban-pending-column"', $html);
        $this->assertCount(2, $parts);
        $remainingHtml = $parts[1];

        $parts2 = explode('data-testid="kanban-in-progress-column"', $remainingHtml);
        $this->assertCount(2, $parts2);
        $pendingHtml = $parts2[0];
        $remainingHtml2 = $parts2[1];

        $parts3 = explode('data-testid="kanban-completed-column"', $remainingHtml2);
        $this->assertCount(2, $parts3);
        $inProgressHtml = $parts3[0];
        $completedHtml = $parts3[1];

        $this->assertStringContainsString('Pending Task Title', $pendingHtml);
        $this->assertStringNotContainsString('InProgress Task Title', $pendingHtml);
        $this->assertStringNotContainsString('Completed Task Title', $pendingHtml);

        $this->assertStringContainsString('InProgress Task Title', $inProgressHtml);
        $this->assertStringNotContainsString('Pending Task Title', $inProgressHtml);
        $this->assertStringNotContainsString('Completed Task Title', $inProgressHtml);

        $this->assertStringContainsString('Completed Task Title', $completedHtml);
        $this->assertStringNotContainsString('Pending Task Title', $completedHtml);
        $this->assertStringNotContainsString('InProgress Task Title', $completedHtml);
    }

    public function test_kanban_button_visibility_in_project_show(): void
    {
        $admin = User::first();

        $projectPending = Project::create([
            'title' => 'Pending Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);

        $projectExecution = Project::create([
            'title' => 'Execution Project',
            'description' => 'Description',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $projectPending));
        $response->assertDontSee('Tablero Kanban');

        $response = $this->actingAs($admin)->get(route('projects.show', $projectExecution));
        $response->assertSee('Tablero Kanban');
    }

    public function test_task_must_belong_to_project_to_be_managed(): void
    {
        $admin = User::first();

        $project1 = Project::create([
            'title' => 'Project 1',
            'description' => 'Desc 1',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $project2 = Project::create([
            'title' => 'Project 2',
            'description' => 'Desc 2',
            'criticality' => 'medium',
            'priority' => 'high',
            'user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $assignee = User::factory()->create(['is_active' => true]);
        $task = $project1->tasks()->create([
            'title' => 'Task belonging to Project 1',
            'due_date' => '2026-07-10',
            'assigned_user_id' => $assignee->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('projects.tasks.edit', [$project2, $task]));
        $response->assertStatus(404);

        $response = $this->actingAs($admin)->put(route('projects.tasks.update', [$project2, $task]), [
            'title' => 'New Title',
            'due_date' => '2026-07-20',
            'assigned_user_id' => $assignee->id,
            'status' => 'pending',
        ]);
        $response->assertStatus(404);

        $response = $this->actingAs($admin)->patch(route('projects.tasks.status.update', [$project2, $task]), [
            'status' => 'in_progress',
        ]);
        $response->assertStatus(404);
    }
}