<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicDashboardLoanVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_dashboard_flash_notification_has_auto_hide_class(): void
    {
        $this->seed();

        $this->withSession([
            'success' => 'Notifikasi uji.',
        ])
            ->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee('js-public-flash-notification');
    }

    public function test_public_dashboard_shows_register_student_button_and_modal(): void
    {
        $this->seed();

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee('Register Siswa')
            ->assertSee('Register Siswa Baru')
            ->assertSee('publicRegisterModal')
            ->assertSee('Pilih kelas')
            ->assertSee('Pilih nama murid')
            ->assertSee('Lanjut')
            ->assertSee('Capture Wajah')
            ->assertSee('Simpan');
    }

    public function test_public_dashboard_contains_return_face_recognition_ui(): void
    {
        $this->seed();

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee('Identitas Pengembali (Face Recognition)')
            ->assertSee('returnFaceVideo')
            ->assertSee('returnFaceStatusBadge')
            ->assertSee('returnSubmitButton')
            ->assertSee('Konfirmasi Pengembalian');
    }

    public function test_public_registration_completes_student_face_enrollment(): void
    {
        $this->seed();

        Storage::fake('public');

        $student = User::query()
            ->where('role', 'student')
            ->where('identity_number', '2024001')
            ->firstOrFail();

        $payload = [
            'public_register_user_id' => $student->id,
            'public_register_kelas' => $student->kelas,
            'public_register_identity_number' => '209998877',
            'public_register_phone' => '081234567890',
            'public_register_image_base64' => $this->sampleFaceImageBase64(),
            'public_register_face_descriptor' => $this->sampleFaceDescriptorJson(0.234567),
            'public_register_step' => 'capture',
        ];

        $this->post(route('dashboard.public.register'), $payload)
            ->assertRedirect(route('dashboard.public'));

        $student->refresh();

        $this->assertSame('209998877', $student->identity_number);
        $this->assertSame('081234567890', $student->phone);
        $this->assertNotNull($student->face_registered_at);
        $this->assertNotEmpty($student->face_encoding);
        $this->assertNotNull($student->face_thumbnail_path);
        Storage::disk('public')->assertExists($student->face_thumbnail_path);
    }

    public function test_public_registration_rejects_duplicate_face_for_other_student(): void
    {
        $this->seed();

        $sourceStudent = User::query()
            ->where('role', 'student')
            ->orderBy('id')
            ->firstOrFail();

        $targetStudent = User::query()
            ->where('role', 'student')
            ->where('id', '!=', $sourceStudent->id)
            ->orderBy('id')
            ->firstOrFail();

        $duplicateEncoding = array_fill(0, 128, 0.234567);

        $sourceStudent->update([
            'face_encoding' => json_encode($duplicateEncoding),
            'face_registered_at' => now(),
        ]);

        $this->post(route('dashboard.public.register'), [
            'public_register_user_id' => $targetStudent->id,
            'public_register_kelas' => $targetStudent->kelas,
            'public_register_identity_number' => 'NISN-' . uniqid(),
            'public_register_phone' => '081234567890',
            'public_register_image_base64' => $this->sampleFaceImageBase64(),
            'public_register_face_descriptor' => $this->sampleFaceDescriptorJson(0.234567),
            'public_register_step' => 'capture',
        ])
            ->assertSessionHasErrors('public_register_image_base64');

        $targetStudent->refresh();

        $this->assertNull($targetStudent->face_registered_at);
        $this->assertNull($targetStudent->face_encoding);
    }

    public function test_public_registration_rejects_when_student_face_data_has_not_been_deleted(): void
    {
        $this->seed();

        $student = User::query()
            ->where('role', 'student')
            ->orderBy('id')
            ->firstOrFail();

        $student->update([
            'face_encoding' => json_encode(array_fill(0, 128, 0.512345)),
            'face_registered_at' => now(),
        ]);

        $this->post(route('dashboard.public.register'), [
            'public_register_user_id' => $student->id,
            'public_register_kelas' => $student->kelas,
            'public_register_identity_number' => 'NISN-' . uniqid(),
            'public_register_phone' => '081234567890',
            'public_register_image_base64' => $this->sampleFaceImageBase64(),
            'public_register_face_descriptor' => $this->sampleFaceDescriptorJson(0.512345),
            'public_register_step' => 'details',
        ])
            ->assertSessionHasErrors('public_register_user_id');

        $student->refresh();

        $this->assertNotNull($student->face_registered_at);
        $this->assertNotEmpty($student->face_encoding);
    }

    public function test_public_registration_rejects_duplicate_nisn_for_other_user(): void
    {
        $this->seed();

        $student = User::query()
            ->where('role', 'student')
            ->where('identity_number', '2024001')
            ->firstOrFail();

        $teacher = User::query()
            ->where('role', 'teacher')
            ->firstOrFail();

        $this->post(route('dashboard.public.register'), [
            'public_register_user_id' => $student->id,
            'public_register_kelas' => $student->kelas,
            'public_register_identity_number' => $teacher->identity_number,
            'public_register_phone' => '081234567890',
            'public_register_image_base64' => $this->sampleFaceImageBase64(),
            'public_register_face_descriptor' => $this->sampleFaceDescriptorJson(0.345678),
            'public_register_step' => 'capture',
        ])
            ->assertSessionHasErrors('public_register_identity_number');

        $student->refresh();

        $this->assertSame('2024001', $student->identity_number);
    }

    public function test_public_dashboard_hides_teacher_loans_and_shows_non_teacher_loans(): void
    {
        $this->seed();

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee('Daftar Barang Dipinjam')
            ->assertSee('Ani (Siswa)')
            ->assertDontSee('Pak Budi (Guru)');
    }

    public function test_admin_dashboard_still_shows_teacher_loans(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee('Pak Budi (Guru)');
    }

    public function test_admin_dashboard_shows_serial_number_and_barcode_in_borrowed_table(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $loan = Loan::query()->where('status', 'active')->with('asset')->firstOrFail();

        if ($loan->asset instanceof Asset) {
            $loan->asset->update([
                'serial_number' => 'SN-TEST-12345',
                'barcode' => 'BC-TEST-67890',
            ]);
        }

        $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee('Stok Barang tersedia')
            ->assertSee('Barang sedang dipinjam')
            ->assertSeeInOrder(['Peminjam', 'Serial Number', 'Kode Barcode'])
            ->assertSee('Serial Number')
            ->assertSee('Kode Barcode')
            ->assertSee('SN-TEST-12345')
            ->assertSee('BC-TEST-67890')
            ->assertSee('stock-laptop-table-wrap');
    }

    private function sampleFaceImageBase64(): string
    {
        $image = imagecreatetruecolor(24, 24);

        if ($image === false) {
            return 'data:image/jpeg;base64,' . base64_encode('fallback-image');
        }

        $backgroundColor = imagecolorallocate($image, 225, 190, 170);
        imagefill($image, 0, 0, $backgroundColor);

        ob_start();
        imagejpeg($image, null, 85);
        $jpegData = ob_get_clean() ?: '';
        imagedestroy($image);

        return 'data:image/jpeg;base64,' . base64_encode($jpegData);
    }

    private function sampleFaceDescriptorJson(float $value): string
    {
        return json_encode(array_fill(0, 128, $value));
    }
}
