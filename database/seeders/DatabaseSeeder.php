<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Loan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate([
            'identity_number' => 'ADM001',
        ], [
            'name' => 'Administrator',
            'role' => 'admin',
            'kelas' => '-',
            'email' => 'admin@inventory.local',
            'phone' => '081200000001',
            'is_active' => true,
            'password' => Hash::make('admin12345'),
            'email_verified_at' => now(),
        ]);

        $teacher = User::query()->updateOrCreate([
            'identity_number' => '19800101',
        ], [
            'name' => 'Pak Budi (Guru)',
            'role' => 'teacher',
            'kelas' => '-',
            'email' => 'guru@inventory.local',
            'phone' => '081200000002',
            'is_active' => true,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $student = User::query()->updateOrCreate([
            'identity_number' => '2024001',
        ], [
            'name' => 'Ani (Siswa)',
            'role' => 'student',
            'kelas' => '10 PPLG 1',
            'email' => 'siswa@inventory.local',
            'phone' => '081200000003',
            'is_active' => true,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        if (User::query()->count() <= 3) {
            User::factory(8)->create();
        }

        $assets = collect([
            ['category' => 'Laptop', 'brand' => 'Lenovo', 'model' => 'ThinkPad X1', 'serial_number' => 'LNV-001', 'status' => 'borrowed'],
            ['category' => 'Laptop', 'brand' => 'Dell', 'model' => 'Inspiron 15', 'serial_number' => 'DEL-001', 'status' => 'available'],
            ['category' => 'Proyektor', 'brand' => 'Epson', 'model' => 'EB-X05', 'serial_number' => 'EPS-001', 'status' => 'available'],
            ['category' => 'Printer', 'brand' => 'Canon', 'model' => 'G2010', 'serial_number' => 'CAN-001', 'status' => 'available'],
            ['category' => 'Aksesoris', 'brand' => 'Logitech', 'model' => 'Wireless Mouse', 'serial_number' => 'LOG-001', 'status' => 'maintenance'],
        ])->map(function (array $asset): Asset {
            return Asset::query()->updateOrCreate([
                'serial_number' => $asset['serial_number'],
            ], [
                'category' => $asset['category'],
                'brand' => $asset['brand'],
                'model' => $asset['model'],
                'barcode' => $asset['serial_number'],
                'qr_code_hash' => hash('sha256', $asset['serial_number']),
                'condition' => 'good',
                'status' => $asset['status'],
            ]);
        });

        if (Loan::query()->doesntExist()) {
            Loan::query()->create([
                'user_id' => $student->id,
                'asset_id' => $assets->firstWhere('serial_number', 'LNV-001')->id,
                'admin_id' => $admin->id,
                'loan_date' => now()->subHours(2),
                'due_date' => now()->addDay(),
                'status' => 'active',
            ]);

            Loan::query()->create([
                'user_id' => $teacher->id,
                'asset_id' => $assets->firstWhere('serial_number', 'DEL-001')->id,
                'admin_id' => $admin->id,
                'loan_date' => now()->subDay(),
                'due_date' => now()->addDays(2),
                'status' => 'active',
            ]);
        }

        Setting::query()->upsert([
            ['setting_key' => 'running_text', 'setting_value' => 'Selamat datang di Sistem Inventaris Laravel'],
            ['setting_key' => 'timezone', 'setting_value' => 'Asia/Jakarta'],
        ], ['setting_key'], ['setting_value']);
    }
}
