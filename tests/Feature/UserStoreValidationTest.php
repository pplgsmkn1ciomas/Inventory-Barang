<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStoreValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_store_requires_phone_number(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->post(route('admin.users.store'), [
            'name' => 'Siswa Baru',
            'identity_number' => '2024999',
            'role' => 'student',
            'kelas' => '10 PPLG 1',
            'email' => 'siswa-baru@example.com',
        ])->assertSessionHasErrors(['phone']);
    }
}