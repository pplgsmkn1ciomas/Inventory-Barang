<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetDynamicOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_asset_with_dynamic_master_data_options(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $sessionPayload = [
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ];

        $this->withSession($sessionPayload)->put(route('admin.settings.menu-a.update'), [
            'categories' => ['Laptop', 'Tablet'],
            'brands' => ['Lenovo', 'Acer'],
            'statuses' => ['available', 'retired'],
            'conditions' => ['good', 'needs_review'],
            'roles' => ['admin', 'teacher', 'student'],
            'classes' => ['-', '10 PPLG 1'],
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'menu-a']));

        $this->withSession($sessionPayload)->post(route('admin.assets.store'), [
            'category' => 'Tablet',
            'brand' => 'Acer',
            'model' => 'Iconia Tab A10',
            'serial_number' => 'TBL-001',
            'barcode' => 'TBL-001',
            'status' => 'retired',
            'condition' => 'needs_review',
        ])->assertRedirect(route('admin.assets.index'));

        $this->assertDatabaseHas('assets', [
            'category' => 'Tablet',
            'brand' => 'Acer',
            'serial_number' => 'TBL-001',
            'status' => 'retired',
            'condition' => 'needs_review',
        ]);
    }
}
