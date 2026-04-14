<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_redirects_to_public_dashboard(): void
    {
        $this->get('/admin/login')
            ->assertRedirect(route('dashboard.public'))
            ->assertSessionHas('show_admin_login', true);
    }

    public function test_admin_can_login_with_default_password(): void
    {
        $this->seed();

        $this->followingRedirects()
            ->post(route('admin.login'), [
                'password' => 'admin12345',
            ])
            ->assertSee('Dashboard Admin');
    }

    public function test_admin_login_rejects_invalid_password(): void
    {
        $this->seed();

        $this->followingRedirects()
            ->post(route('admin.login'), [
                'password' => 'wrong-password',
            ])
            ->assertSee('Password admin tidak valid.');
    }
}