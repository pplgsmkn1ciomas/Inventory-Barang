<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDynamicOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_user_with_dynamic_role_and_class_options(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $this->withSession($this->adminSession($admin))->put(route('admin.settings.menu-a.update'), [
            'categories' => ['Laptop', 'Proyektor'],
            'brands' => ['Lenovo', 'Acer'],
            'statuses' => ['available', 'borrowed'],
            'conditions' => ['good', 'minor_damage'],
            'roles' => ['admin', 'teacher', 'student', 'staff'],
            'classes' => ['-', 'Staf TU', 'Guru'],
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'menu-a']));

        $this->withSession($this->adminSession($admin))->post(route('admin.users.store'), [
            'name' => 'Operator TU',
            'identity_number' => 'USR-DYN-001',
            'role' => 'staff',
            'kelas' => 'Staf TU',
            'phone' => '081299900001',
            'email' => null,
            'password' => null,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'identity_number' => 'USR-DYN-001',
            'name' => 'Operator TU',
            'role' => 'staff',
            'kelas' => 'Staf TU',
            'phone' => '081299900001',
        ]);
    }

    public function test_menu_a_update_rejects_role_options_without_admin(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $this->withSession($this->adminSession($admin))->put(route('admin.settings.menu-a.update'), [
            'categories' => ['Laptop'],
            'brands' => ['Lenovo'],
            'statuses' => ['available'],
            'conditions' => ['good'],
            'roles' => ['teacher', 'student'],
            'classes' => ['10 PPLG 1'],
        ])->assertSessionHasErrors(['roles']);
    }

    /**
     * @return array<string, array<string, int|string>>
     */
    private function adminSession(User $admin): array
    {
        return [
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ];
    }
}
