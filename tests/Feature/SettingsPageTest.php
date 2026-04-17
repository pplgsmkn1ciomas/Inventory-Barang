<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_settings_page(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan')
            ->assertSee('Running Teks')
            ->assertSee('Master Data Sistem')
            ->assertSee('Menu B')
            ->assertSee('Kamera')
            ->assertSee('faceCameraFrameModeInput')
            ->assertSee('faceCameraHorizontalShiftInput')
            ->assertSee('faceCameraVerticalShiftInput')
            ->assertSee('faceCameraPreviewFallback')
            ->assertSee('faceCameraPreviewStatusBadge')
            ;
    }

    public function test_admin_can_update_running_text_setting(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $newRunningText = 'Pastikan semua aset kembali sebelum jam 15.00 WIB.';

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->put(route('admin.settings.running-text.update'), [
            'running_text' => $newRunningText,
            'public_reminder_enabled' => '1',
            'public_reminder_background' => '#111111',
            'public_reminder_text_color' => '#f1f1f1',
            'public_running_text_speed' => '18',
            'public_running_text_font_size' => '20',
            'public_running_text_font_family' => 'georgia',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'running-text']));

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_running_text',
            'setting_value' => $newRunningText,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_reminder_background',
            'setting_value' => '#111111',
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_reminder_text_color',
            'setting_value' => '#f1f1f1',
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_reminder_enabled',
            'setting_value' => '1',
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_running_text_speed',
            'setting_value' => '18',
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_running_text_font_size',
            'setting_value' => '20',
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_running_text_font_family',
            'setting_value' => 'georgia',
        ]);

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee($newRunningText);
    }

    public function test_admin_can_disable_running_text_from_running_text_tab(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->put(route('admin.settings.running-text.update'), [
            'running_text' => 'Teks tetap tersimpan meskipun banner dimatikan.',
            'public_reminder_enabled' => '0',
            'public_reminder_background' => '#0a0a0a',
            'public_reminder_text_color' => '#ffffff',
            'public_running_text_speed' => '15',
            'public_running_text_font_size' => '17',
            'public_running_text_font_family' => 'system',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'running-text']));

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_reminder_enabled',
            'setting_value' => '0',
        ]);

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertDontSee('aria-label="Pengumuman waktu pengembalian barang"', false);
    }

    public function test_admin_can_update_menu_a_asset_master_data_settings(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $categories = ['Laptop', 'Proyektor', 'Tablet'];
        $brands = ['Lenovo', 'Acer', 'Asus'];
        $statuses = ['available', 'borrowed', 'retired'];
        $conditions = ['good', 'minor_damage', 'needs_review'];
        $roles = ['admin', 'teacher', 'student', 'staff'];
        $classes = ['-', '10 PPLG 1', '11 TKJ 1'];

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->put(route('admin.settings.menu-a.update'), [
            'categories' => $categories,
            'brands' => $brands,
            'statuses' => $statuses,
            'conditions' => $conditions,
            'roles' => $roles,
            'classes' => $classes,
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'menu-a']));

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'asset_categories',
            'setting_value' => json_encode($categories),
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'asset_brands',
            'setting_value' => json_encode($brands),
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'asset_statuses',
            'setting_value' => json_encode($statuses),
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'asset_conditions',
            'setting_value' => json_encode($conditions),
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'user_roles',
            'setting_value' => json_encode($roles),
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'user_classes',
            'setting_value' => json_encode($classes),
        ]);

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.assets.index'))
            ->assertOk()
            ->assertSee('Tablet')
            ->assertSee('Acer')
            ->assertSee('Retired')
            ->assertSee('Needs Review');

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('staff')
            ->assertSee('11 TKJ 1');
    }

    public function test_admin_can_update_menu_b_header_settings(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $headerTitle = 'Dashboard Publik Sekolah';
        $headerSubtitle = 'Informasi peminjaman aset harian.';
        $borrowLabel = 'Ajukan Peminjaman';
        $returnLabel = 'Catat Pengembalian';

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->put(route('admin.settings.menu-b.update'), [
            'public_header_title' => $headerTitle,
            'public_header_subtitle' => $headerSubtitle,
            'public_borrow_button_label' => $borrowLabel,
            'public_return_button_label' => $returnLabel,
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'menu-b']));

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_header_title',
            'setting_value' => $headerTitle,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_header_subtitle',
            'setting_value' => $headerSubtitle,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_borrow_button_label',
            'setting_value' => $borrowLabel,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_return_button_label',
            'setting_value' => $returnLabel,
        ]);

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee($headerTitle)
            ->assertSee($headerSubtitle)
            ->assertSee($borrowLabel)
            ->assertSee($returnLabel);
    }

    public function test_admin_can_update_menu_c_camera_preview_settings(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $previewSize = '520';
        $captureSize = '640';
        $borderRadius = '24';
        $background = '#0b1220';
        $objectFit = 'contain';
        $frameMode = 'wide';
        $horizontalShift = '25';
        $verticalShift = '-15';

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->put(route('admin.settings.menu-c.update'), [
            'face_camera_preview_size' => $previewSize,
            'face_camera_capture_size' => $captureSize,
            'face_camera_border_radius' => $borderRadius,
            'face_camera_background' => $background,
            'face_camera_object_fit' => $objectFit,
            'face_camera_frame_mode' => $frameMode,
            'face_camera_horizontal_shift' => $horizontalShift,
            'face_camera_vertical_shift' => $verticalShift,
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'menu-c']));

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'face_camera_preview_size',
            'setting_value' => $previewSize,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'face_camera_capture_size',
            'setting_value' => $captureSize,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'face_camera_border_radius',
            'setting_value' => $borderRadius,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'face_camera_background',
            'setting_value' => strtolower($background),
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'face_camera_object_fit',
            'setting_value' => $objectFit,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'face_camera_frame_mode',
            'setting_value' => $frameMode,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'face_camera_horizontal_shift',
            'setting_value' => $horizontalShift,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'face_camera_vertical_shift',
            'setting_value' => $verticalShift,
        ]);

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.face-register.index'))
            ->assertOk()
            ->assertSee('--face-camera-preview-size: 520px')
            ->assertSee('--face-camera-border-radius: 24px')
            ->assertSee('--face-camera-background: #0b1220')
            ->assertSee('--face-camera-object-fit: contain')
            ->assertSee('--face-camera-frame-ratio: 4 / 3')
            ->assertSee('--face-camera-horizontal-shift: 25%')
            ->assertSee('--face-camera-vertical-shift: -15%');

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee('--face-camera-preview-size: 520px')
            ->assertSee('--face-camera-border-radius: 24px')
            ->assertSee('--face-camera-frame-ratio: 4 / 3')
            ->assertSee('--face-camera-horizontal-shift: 25%')
            ->assertSee('--face-camera-vertical-shift: -15%');
    }
}
