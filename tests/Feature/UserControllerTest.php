<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndAdminSeeder::class);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $nonAdmin = User::factory()->create();
        $nonAdmin->assignRole('operations');

        $response = $this->actingAs($nonAdmin)->get('/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::first();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
        $response->assertSee('Usuarios');
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::first();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New Staff',
            'email' => 'staff@asada.org',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'operations',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'staff@asada.org',
            'is_active' => true,
        ]);

        $user = User::where('email', 'staff@asada.org')->first();
        $this->assertTrue($user->hasRole('operations'));
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::first();
        $user = User::factory()->create();
        $user->assignRole('operations');

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@asada.org',
            'role' => 'fiscal',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@asada.org',
            'is_active' => false,
        ]);

        $user->refresh();
        $this->assertTrue($user->hasRole('fiscal'));
    }

    public function test_admin_cannot_deactivate_themselves(): void
    {
        $admin = User::first();

        $response = $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'admin',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'No puedes desactivar tu propia cuenta.');
        $admin->refresh();
        $this->assertTrue($admin->is_active);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::first();

        $response = $this->actingAs($admin)->delete(route('users.destroy', $admin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'No puedes eliminar tu propia cuenta.');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }
}
