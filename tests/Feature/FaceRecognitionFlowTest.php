<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FaceRecognitionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_face_register_page(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $this->withSession($this->adminSession($admin))
            ->get(route('admin.face-register.index'))
            ->assertOk()
            ->assertSee('Register Wajah Pengguna')
            ->assertSee('Mulai Scan Wajah');
    }

    public function test_admin_can_register_face_encoding_for_user(): void
    {
        $this->seed();

        Storage::fake('public');

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetUser = User::query()->where('role', '!=', 'admin')->firstOrFail();

        $this->withSession($this->adminSession($admin))
            ->postJson(route('admin.face-register.store'), [
                'user_id' => $targetUser->id,
                'image_base64' => $this->sampleFaceImageBase64(),
                'face_descriptor' => $this->sampleFaceDescriptorJson(0.123456),
            ])
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $targetUser->refresh();

        $this->assertNotNull($targetUser->face_registered_at);
        $this->assertNotEmpty($targetUser->face_encoding);
        $this->assertNotNull($targetUser->face_thumbnail_path);
        Storage::disk('public')->assertExists($targetUser->face_thumbnail_path);
    }

    public function test_admin_can_open_face_register_page_with_selected_user(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetUser = User::query()->where('role', '!=', 'admin')->firstOrFail();

        $targetUser->update([
            'face_encoding' => json_encode(array_fill(0, 128, 0.111111)),
            'face_registered_at' => now()->subDay(),
        ]);

        $this->withSession($this->adminSession($admin))
            ->get(route('admin.face-register.index', ['user_id' => $targetUser->id]))
            ->assertOk()
            ->assertSee('Data wajah sudah terdaftar')
            ->assertSee('Hapus data wajah terlebih dahulu')
            ->assertSee($targetUser->name);
    }

    public function test_admin_registration_rejects_when_user_already_has_face_data(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetUser = User::query()->where('role', 'student')->orderBy('id')->firstOrFail();

        $targetUser->update([
            'face_encoding' => json_encode(array_fill(0, 128, 0.111111)),
            'face_registered_at' => now()->subDay(),
        ]);

        $this->withSession($this->adminSession($admin))
            ->postJson(route('admin.face-register.store'), [
                'user_id' => $targetUser->id,
                'image_base64' => $this->sampleFaceImageBase64(),
                'face_descriptor' => $this->sampleFaceDescriptorJson(0.111111),
            ])
            ->assertStatus(409)
            ->assertJsonPath('status', 'face_already_registered')
            ->assertJsonPath('user.id', $targetUser->id);

        $targetUser->refresh();

        $this->assertNotNull($targetUser->face_registered_at);
        $this->assertSame(array_fill(0, 128, 0.111111), json_decode((string) $targetUser->face_encoding, true));
    }

    public function test_admin_registration_allows_stale_timestamp_without_face_payload(): void
    {
        $this->seed();

        Storage::fake('public');

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetUser = User::query()->where('role', 'student')->orderBy('id')->firstOrFail();

        $targetUser->update([
            'face_encoding' => null,
            'face_registered_at' => now()->subDay(),
            'face_thumbnail_path' => null,
        ]);

        $this->withSession($this->adminSession($admin))
            ->postJson(route('admin.face-register.store'), [
                'user_id' => $targetUser->id,
                'image_base64' => $this->sampleFaceImageBase64(),
                'face_descriptor' => $this->sampleFaceDescriptorJson(0.515151),
            ])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('user.id', $targetUser->id);

        $targetUser->refresh();

        $this->assertNotNull($targetUser->face_registered_at);
        $this->assertSame(array_fill(0, 128, 0.515151), json_decode((string) $targetUser->face_encoding, true));
        $this->assertNotNull($targetUser->face_thumbnail_path);
    }

    public function test_admin_registration_rejects_duplicate_face_for_other_user(): void
    {
        $this->seed();

        Storage::fake('public');

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $sourceUser = User::query()->where('role', 'student')->orderBy('id')->firstOrFail();
        $targetUser = User::query()
            ->where('role', 'student')
            ->where('id', '!=', $sourceUser->id)
            ->orderBy('id')
            ->firstOrFail();

        $duplicateEncoding = array_fill(0, 128, 0.654321);

        $sourceUser->update([
            'face_encoding' => json_encode($duplicateEncoding),
            'face_registered_at' => now(),
        ]);

        $this->withSession($this->adminSession($admin))
            ->postJson(route('admin.face-register.store'), [
                'user_id' => $targetUser->id,
                'image_base64' => $this->sampleFaceImageBase64(),
                'face_descriptor' => $this->sampleFaceDescriptorJson(0.654321),
            ])
            ->assertStatus(409)
            ->assertJsonPath('status', 'duplicate_face')
            ->assertJsonPath('user.id', $sourceUser->id);

        $targetUser->refresh();

        $this->assertNull($targetUser->face_registered_at);
        $this->assertNull($targetUser->face_encoding);
        $this->assertNull($targetUser->face_thumbnail_path);
    }

    public function test_admin_can_register_face_again_after_deletion(): void
    {
        $this->seed();

        Storage::fake('public');

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetUser = User::query()->where('role', 'student')->orderBy('id')->firstOrFail();

        $targetUser->update([
            'face_encoding' => json_encode(array_fill(0, 128, 0.111111)),
            'face_registered_at' => now()->subDay(),
            'face_thumbnail_path' => 'face-thumbnails/test-thumb.jpg',
        ]);

        Storage::disk('public')->put('face-thumbnails/test-thumb.jpg', 'fake-image');

        $this->withSession($this->adminSession($admin))
            ->delete(route('admin.users.face-thumbnail.destroy', $targetUser))
            ->assertRedirect(route('admin.users.index'));

        $this->withSession($this->adminSession($admin))
            ->postJson(route('admin.face-register.store'), [
                'user_id' => $targetUser->id,
                'image_base64' => $this->sampleFaceImageBase64(),
                'face_descriptor' => $this->sampleFaceDescriptorJson(0.777777),
            ])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('user.id', $targetUser->id);

        $targetUser->refresh();

        $this->assertNotNull($targetUser->face_registered_at);
        $this->assertSame(array_fill(0, 128, 0.777777), json_decode((string) $targetUser->face_encoding, true));
        $this->assertNotNull($targetUser->face_thumbnail_path);
        Storage::disk('public')->assertExists($targetUser->face_thumbnail_path);
    }

    public function test_admin_can_see_face_preview_action_in_user_table(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetUser = User::query()->where('role', '!=', 'admin')->firstOrFail();

        $targetUser->update([
            'face_encoding' => json_encode(array_fill(0, 128, 0.333333)),
            'face_registered_at' => now(),
            'face_thumbnail_path' => 'face-thumbnails/test-thumb.jpg',
        ]);

        $this->withSession($this->adminSession($admin))
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Preview Wajah')
            ->assertSee('Preview Capture Wajah')
            ->assertSee('storage/face-thumbnails/test-thumb.jpg')
            ->assertSee('btn-info')
                ->assertSee('Hapus Data Wajah')
            ->assertSee(route('admin.face-register.index', ['user_id' => $targetUser->id]));
    }

    public function test_admin_can_delete_face_data_from_preview_modal(): void
    {
        $this->seed();

        Storage::fake('public');

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetUser = User::query()->where('role', '!=', 'admin')->firstOrFail();

        Storage::disk('public')->put('face-thumbnails/test-thumb.jpg', 'fake-image');

        $targetUser->update([
            'face_encoding' => json_encode(array_fill(0, 128, 0.333333)),
            'face_registered_at' => now(),
            'face_thumbnail_path' => 'face-thumbnails/test-thumb.jpg',
        ]);

        $this->withSession($this->adminSession($admin))
            ->delete(route('admin.users.face-thumbnail.destroy', $targetUser))
            ->assertRedirect(route('admin.users.index'));

        $targetUser->refresh();

        $this->assertNull($targetUser->face_encoding);
        $this->assertNull($targetUser->face_registered_at);
        $this->assertNull($targetUser->face_thumbnail_path);
        Storage::disk('public')->assertMissing('face-thumbnails/test-thumb.jpg');
    }

    public function test_register_face_rejects_invalid_descriptor_payload(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $targetUser = User::query()->where('role', '!=', 'admin')->firstOrFail();

        $this->withSession($this->adminSession($admin))
            ->postJson(route('admin.face-register.store'), [
                'user_id' => $targetUser->id,
                'image_base64' => 'data:image/jpeg;base64,' . base64_encode('dummy-image'),
                'face_descriptor' => 'not-a-valid-descriptor',
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'invalid_descriptor');
    }

    public function test_recognize_endpoint_returns_matched_user(): void
    {
        $this->seed();

        $targetUser = User::query()->where('role', '!=', 'admin')->firstOrFail();

        $targetUser->update([
            'face_encoding' => json_encode(array_fill(0, 128, 0.223344)),
            'face_registered_at' => now(),
        ]);

        $this->postJson(route('face-recognition.recognize'), [
            'face_descriptor' => $this->sampleFaceDescriptorJson(0.223344),
        ])
            ->assertOk()
            ->assertJsonPath('status', 'matched')
            ->assertJsonPath('recognized', true)
            ->assertJsonPath('user.id', $targetUser->id)
            ->assertJsonPath('user.identity_number', $targetUser->identity_number);
    }

    public function test_recognize_endpoint_returns_unknown_when_not_matched(): void
    {
        $this->seed();

        $targetUser = User::query()->where('role', '!=', 'admin')->firstOrFail();

        $targetUser->update([
            'face_encoding' => json_encode(array_fill(0, 128, 0.556677)),
            'face_registered_at' => now(),
        ]);

        $this->postJson(route('face-recognition.recognize'), [
            'face_descriptor' => $this->sampleFaceDescriptorJson(0.112233),
        ])
            ->assertOk()
            ->assertJsonPath('status', 'unknown')
            ->assertJsonPath('recognized', false);
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
