<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GlobalAuditControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
    }

    public function test_admin_can_view_global_audit_history()
    {
        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->first();

        AuditLog::create([
            'user_id' => $admin->id,
            'action_type' => 'test_action',
            'auditable_type' => 'App\Models\Project',
            'auditable_id' => 999,
        ]);

        $response = $this->actingAs($admin)->get(route('audit.index'));

        $response->assertStatus(200);
        $response->assertViewIs('audit.index');
        $response->assertSee('Historial Global de Acciones Críticas');
        $response->assertSee('Test_action');
    }

    public function test_non_admin_cannot_view_global_audit_history()
    {
        $user = User::factory()->create();
        $user->assignRole('fiscal');

        $response = $this->actingAs($user)->get(route('audit.index'));

        $response->assertStatus(403);
    }
}

