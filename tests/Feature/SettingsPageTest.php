<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\Loan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            ->assertSee('Dashboard')
            ->assertSee('Kamera')
            ->assertSee('Data Pengguna')
            ->assertSee('Admin')
            ->assertSee('Log')
            ->assertSee('cleanupLogMasterModal')
            ->assertSee('Verifikasi Master Emergency')
            ->assertSee('bulkDeleteUserModal')
            ->assertSee('Informasi Hapus Massal')
            ->assertSee('bulkDeleteUserInfoModal')
            ->assertSee('Pengaturan Akun Admin')
            ->assertSee('Ubah Password Admin')
            ->assertSee('faceCameraFrameModeInput')
            ->assertSee('faceCameraHorizontalShiftInput')
            ->assertSee('faceCameraVerticalShiftInput')
            ->assertSee('faceCameraDebugEnabledInput')
            ->assertSee('faceCameraPreviewFallback')
            ->assertSee('faceCameraPreviewStatusBadge')
            ;
    }

    public function test_admin_can_update_own_password_from_admin_tab(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $newPassword = 'AdminBaru123!';

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->put(route('admin.settings.admin-password.update'), [
            'current_password' => 'admin12345',
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'admin']))
            ->assertSessionHas('success', 'Password admin berhasil diperbarui.');

        $admin->refresh();

        $this->assertTrue(Hash::check($newPassword, (string) $admin->password));

        $this->assertTrue(
            ActivityLog::query()
                ->where('action', 'UPDATE')
                ->where('table_name', 'users')
                ->where('data', 'like', '%password akun admin%')
                ->exists()
        );
    }

    public function test_admin_password_update_rejects_invalid_current_password(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->put(route('admin.settings.admin-password.update'), [
            'current_password' => 'password-lama-salah',
            'new_password' => 'PasswordBaru123!',
            'new_password_confirmation' => 'PasswordBaru123!',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'admin']))
            ->assertSessionHasErrors('current_password');

        $admin->refresh();

        $this->assertTrue(Hash::check('admin12345', (string) $admin->password));
    }

    public function test_admin_can_open_log_tab_and_view_activity_logs(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        ActivityLog::query()->create([
            'timestamp' => now()->subMinutes(10),
            'action' => 'BORROW',
            'table_name' => 'loans',
            'data' => 'Uji log borrow tabel loans.',
            'details' => 'detail-borrow-uji',
            'user_agent' => 'phpunit-agent',
        ]);

        ActivityLog::query()->create([
            'timestamp' => now()->subMinutes(5),
            'action' => 'UPDATE',
            'table_name' => 'settings',
            'data' => 'Uji log update menu settings.',
            'details' => 'detail-update-uji',
            'user_agent' => 'phpunit-agent',
        ]);

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.settings.index', ['tab' => 'log']))
            ->assertOk()
            ->assertSee('Log Aktivitas')
            ->assertSee('Export Excel')
            ->assertSee('Cleanup Log')
            ->assertSee('Total Aktivitas')
            ->assertSee('7 Hari Terakhir')
            ->assertSee('Uji log borrow tabel loans.')
            ->assertSee('Uji log update menu settings.')
            ->assertSee('phpunit-agent');
    }

    public function test_admin_can_filter_activity_logs_by_action_in_log_tab(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        ActivityLog::query()->create([
            'timestamp' => now()->subMinutes(8),
            'action' => 'BORROW',
            'table_name' => 'loans',
            'data' => 'Borrow only log entry.',
            'details' => 'borrow-entry',
            'user_agent' => 'phpunit-agent',
        ]);

        ActivityLog::query()->create([
            'timestamp' => now()->subMinutes(3),
            'action' => 'UPDATE',
            'table_name' => 'settings',
            'data' => 'Update only log entry.',
            'details' => 'update-entry',
            'user_agent' => 'phpunit-agent',
        ]);

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.settings.index', [
            'tab' => 'log',
            'log_action' => 'UPDATE',
        ]))
            ->assertOk()
            ->assertSee('Update only log entry.')
            ->assertDontSee('Borrow only log entry.');
    }

    public function test_admin_can_export_activity_logs_excel_from_log_tab(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        ActivityLog::query()->create([
            'timestamp' => now()->subMinutes(4),
            'action' => 'BORROW',
            'table_name' => 'loans',
            'data' => 'Export log borrow entry.',
            'details' => 'borrow-export',
            'user_agent' => 'phpunit-agent',
        ]);

        ActivityLog::query()->create([
            'timestamp' => now()->subMinutes(2),
            'action' => 'UPDATE',
            'table_name' => 'settings',
            'data' => 'Export log update entry.',
            'details' => 'update-export',
            'user_agent' => 'phpunit-agent',
        ]);

        $response = $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.settings.logs.export', [
            'log_action' => 'UPDATE',
            'log_table' => 'settings',
            'log_search' => 'update',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type');
        $response->assertHeader('content-disposition');

        $this->assertStringContainsString(
            '.xlsx',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_admin_can_cleanup_activity_logs_by_date_range_with_password(): void
    {
        $this->seed();
        config()->set('auth.master_admin_password_hash', Hash::make('master-cleanup-123'));

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $oldLogOne = ActivityLog::query()->create([
            'timestamp' => now()->subDays(15),
            'action' => 'UPDATE',
            'table_name' => 'settings',
            'data' => 'Old log entry 1',
            'details' => 'cleanup-target-1',
            'user_agent' => 'phpunit-agent',
        ]);

        $oldLogTwo = ActivityLog::query()->create([
            'timestamp' => now()->subDays(12),
            'action' => 'BORROW',
            'table_name' => 'loans',
            'data' => 'Old log entry 2',
            'details' => 'cleanup-target-2',
            'user_agent' => 'phpunit-agent',
        ]);

        $recentLog = ActivityLog::query()->create([
            'timestamp' => now()->subDays(2),
            'action' => 'RETURN',
            'table_name' => 'loans',
            'data' => 'Recent log entry',
            'details' => 'keep-this-log',
            'user_agent' => 'phpunit-agent',
        ]);

        $response = $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->delete(route('admin.settings.logs.cleanup'), [
            'cleanup_date_from' => now()->subDays(20)->toDateString(),
            'cleanup_date_to' => now()->subDays(10)->toDateString(),
            'cleanup_password' => 'admin12345',
            'cleanup_master_password' => 'master-cleanup-123',
            'cleanup_confirm' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'log']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('activity_logs', ['id' => $oldLogOne->id]);
        $this->assertDatabaseMissing('activity_logs', ['id' => $oldLogTwo->id]);
        $this->assertDatabaseHas('activity_logs', ['id' => $recentLog->id]);

        $this->assertTrue(
            ActivityLog::query()
                ->where('action', 'BULK_DELETE')
                ->where('table_name', 'activity_logs')
                ->exists()
        );
    }

    public function test_cleanup_activity_logs_requires_valid_admin_password(): void
    {
        $this->seed();
        config()->set('auth.master_admin_password_hash', Hash::make('master-cleanup-123'));

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetLog = ActivityLog::query()->create([
            'timestamp' => now()->subDays(30),
            'action' => 'UPDATE',
            'table_name' => 'settings',
            'data' => 'Protected cleanup log',
            'details' => 'must-not-delete-on-wrong-pass',
            'user_agent' => 'phpunit-agent',
        ]);

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->delete(route('admin.settings.logs.cleanup'), [
            'cleanup_date_from' => now()->subDays(40)->toDateString(),
            'cleanup_date_to' => now()->subDays(20)->toDateString(),
            'cleanup_password' => 'password-salah',
            'cleanup_master_password' => 'master-cleanup-123',
            'cleanup_confirm' => '1',
        ])
            ->assertRedirect(route('admin.settings.index', ['tab' => 'log']))
            ->assertSessionHasErrors('cleanup_password');

        $this->assertDatabaseHas('activity_logs', ['id' => $targetLog->id]);
    }

    public function test_cleanup_activity_logs_requires_valid_master_emergency_password(): void
    {
        $this->seed();
        config()->set('auth.master_admin_password_hash', Hash::make('master-cleanup-123'));

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetLog = ActivityLog::query()->create([
            'timestamp' => now()->subDays(14),
            'action' => 'UPDATE',
            'table_name' => 'settings',
            'data' => 'Master protected cleanup log',
            'details' => 'must-not-delete-on-invalid-master',
            'user_agent' => 'phpunit-agent',
        ]);

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->delete(route('admin.settings.logs.cleanup'), [
            'cleanup_date_from' => now()->subDays(20)->toDateString(),
            'cleanup_date_to' => now()->subDays(10)->toDateString(),
            'cleanup_password' => 'admin12345',
            'cleanup_master_password' => 'master-salah',
            'cleanup_confirm' => '1',
        ])
            ->assertRedirect(route('admin.settings.index', ['tab' => 'log']))
            ->assertSessionHasErrors('cleanup_master_password');

        $this->assertDatabaseHas('activity_logs', ['id' => $targetLog->id]);
    }

    public function test_admin_can_bulk_delete_users_by_selected_class_from_settings_tab(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetClass = '12 RPL 9';

        $userOne = User::query()->create([
            'name' => 'Uji Hapus 1',
            'identity_number' => 'DELCLS001',
            'role' => 'student',
            'kelas' => $targetClass,
            'email' => 'delcls001@example.test',
            'phone' => '081234560001',
            'is_active' => true,
            'password' => 'password123',
        ]);

        $userTwo = User::query()->create([
            'name' => 'Uji Hapus 2',
            'identity_number' => 'DELCLS002',
            'role' => 'teacher',
            'kelas' => $targetClass,
            'email' => 'delcls002@example.test',
            'phone' => '081234560002',
            'is_active' => true,
            'password' => 'password123',
        ]);

        $response = $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->delete(route('admin.settings.user-data.bulk-delete'), [
            'bulk_delete_class' => $targetClass,
            'bulk_delete_confirm' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'user-data']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $userOne->id]);
        $this->assertDatabaseMissing('users', ['id' => $userTwo->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_bulk_delete_users_by_class_skips_admin_and_users_with_loans(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetClass = '11 UJI 1';

        $protectedAdmin = User::query()->create([
            'name' => 'Admin Kelas Uji',
            'identity_number' => 'ADMCLS001',
            'role' => 'admin',
            'kelas' => $targetClass,
            'email' => 'admcls001@example.test',
            'phone' => '081234560010',
            'is_active' => true,
            'password' => 'password123',
        ]);

        $lockedUser = User::query()->create([
            'name' => 'User Pinjaman Uji',
            'identity_number' => 'LOCKCLS001',
            'role' => 'student',
            'kelas' => $targetClass,
            'email' => 'lockcls001@example.test',
            'phone' => '081234560011',
            'is_active' => true,
            'password' => 'password123',
        ]);

        $deletableUser = User::query()->create([
            'name' => 'User Dihapus Uji',
            'identity_number' => 'FREECLS001',
            'role' => 'student',
            'kelas' => $targetClass,
            'email' => 'freecls001@example.test',
            'phone' => '081234560012',
            'is_active' => true,
            'password' => 'password123',
        ]);

        $asset = Asset::query()->create([
            'category' => 'Laptop',
            'brand' => 'Uji',
            'model' => 'Aset Kunci',
            'serial_number' => 'LOCK-ASSET-001',
            'barcode' => 'LOCK-ASSET-001',
            'qr_code_hash' => hash('sha256', 'LOCK-ASSET-001'),
            'condition' => 'good',
            'status' => 'borrowed',
        ]);

        Loan::query()->create([
            'user_id' => $lockedUser->id,
            'asset_id' => $asset->id,
            'admin_id' => $admin->id,
            'loan_date' => now()->subHour(),
            'due_date' => now()->addDay(),
            'status' => 'active',
        ]);

        $response = $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->delete(route('admin.settings.user-data.bulk-delete'), [
            'bulk_delete_class' => $targetClass,
            'bulk_delete_confirm' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'user-data']));
        $response->assertSessionHas('success', static fn (string $message): bool => str_contains($message, 'akun admin dilewati') && str_contains($message, 'riwayat pinjaman tidak dihapus'));

        $this->assertDatabaseHas('users', ['id' => $protectedAdmin->id]);
        $this->assertDatabaseHas('users', ['id' => $lockedUser->id]);
        $this->assertDatabaseMissing('users', ['id' => $deletableUser->id]);
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

    public function test_update_running_text_records_activity_log_entry(): void
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
            'running_text' => 'Log running text uji aktivitas.',
            'public_reminder_enabled' => '1',
            'public_reminder_background' => '#222222',
            'public_reminder_text_color' => '#fefefe',
            'public_running_text_speed' => '16',
            'public_running_text_font_size' => '18',
            'public_running_text_font_family' => 'system',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'running-text']));

        $this->assertTrue(
            ActivityLog::query()
                ->where('action', 'UPDATE')
                ->where('table_name', 'settings')
                ->where('data', 'like', '%running teks%')
                ->exists()
        );
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
        Storage::fake('public');

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $headerTitle = 'Dashboard Publik Sekolah';
        $headerSubtitle = 'Informasi peminjaman aset harian.';
        $borrowLabel = 'Ajukan Peminjaman';
        $returnLabel = 'Catat Pengembalian';
        $publicNavLogoFile = UploadedFile::fake()->image('logo-public.png', 180, 180);
        $publicNavBrandText = 'SI-BAR';
        $publicNavBrandSubtext = 'Pusat Inventaris Sekolah';
        $adminNavLogoFile = UploadedFile::fake()->image('logo-admin.png', 180, 180);
        $adminNavBrandTitle = 'Inventory Center';
        $adminNavBrandSubtitle = 'SMK NEGERI 1 CIOMAS';

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
            'public_nav_logo_file' => $publicNavLogoFile,
            'public_nav_brand_text' => $publicNavBrandText,
            'public_nav_brand_subtext' => $publicNavBrandSubtext,
            'admin_nav_logo_file' => $adminNavLogoFile,
            'admin_nav_brand_title' => $adminNavBrandTitle,
            'admin_nav_brand_subtitle' => $adminNavBrandSubtitle,
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

        $publicLogoPath = (string) Setting::query()->where('setting_key', 'public_nav_logo_path')->value('setting_value');
        $this->assertNotSame('', $publicLogoPath);
        $this->assertStringStartsWith('branding/public/', $publicLogoPath);
        Storage::disk('public')->assertExists($publicLogoPath);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_nav_brand_text',
            'setting_value' => $publicNavBrandText,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'public_nav_brand_subtext',
            'setting_value' => $publicNavBrandSubtext,
        ]);

        $adminLogoPath = (string) Setting::query()->where('setting_key', 'admin_nav_logo_path')->value('setting_value');
        $this->assertNotSame('', $adminLogoPath);
        $this->assertStringStartsWith('branding/admin/', $adminLogoPath);
        Storage::disk('public')->assertExists($adminLogoPath);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'admin_nav_brand_title',
            'setting_value' => $adminNavBrandTitle,
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'admin_nav_brand_subtitle',
            'setting_value' => $adminNavBrandSubtitle,
        ]);

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee($headerTitle)
            ->assertSee($headerSubtitle)
            ->assertSee($borrowLabel)
            ->assertSee($returnLabel)
            ->assertSee($publicNavBrandText)
            ->assertSee($publicNavBrandSubtext)
            ->assertSee(Storage::url($publicLogoPath), false);

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee($adminNavBrandTitle)
            ->assertSee($adminNavBrandSubtitle)
            ->assertSee(Storage::url($adminLogoPath), false);
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
        $debugEnabled = '0';

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
            'face_camera_debug_enabled' => $debugEnabled,
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

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'face_camera_debug_enabled',
            'setting_value' => $debugEnabled,
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
            ->assertSee('--face-camera-vertical-shift: -15%')
            ->assertDontSee('id="borrowFaceDebugPanel"', false)
            ->assertDontSee('id="returnFaceDebugPanel"', false);
    }
}
