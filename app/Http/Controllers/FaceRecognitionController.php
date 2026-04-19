<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Services\FaceDescriptorService;
use App\Services\FaceEncodingMatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionController extends Controller
{
    public function __construct(
        private readonly FaceDescriptorService $faceDescriptorService,
        private readonly FaceEncodingMatcher $faceEncodingMatcher,
    ) {
    }

    public function index(Request $request): View
    {
        $faceCameraSettings = $this->resolveFaceCameraSettings();

        $users = User::query()
            ->select([
                'id',
                'name',
                'identity_number',
                'kelas',
                'face_registered_at',
                'face_thumbnail_path',
            ])
            ->selectRaw("CASE WHEN face_thumbnail_path IS NOT NULL OR (face_encoding IS NOT NULL AND TRIM(face_encoding) <> '' AND TRIM(face_encoding) <> '[]') THEN 1 ELSE 0 END as has_face_data")
            ->orderBy('name')
            ->get();

        $selectedUserId = (int) $request->query('user_id', 0);
        $selectedUser = $selectedUserId > 0
            ? User::query()->find($selectedUserId, ['id', 'name', 'identity_number', 'kelas', 'face_registered_at', 'face_encoding', 'face_thumbnail_path'])
            : null;

        return view('admin.face-register.index', [
            'users' => $users,
            'selectedUserId' => $selectedUserId,
            'selectedUser' => $selectedUser,
            'faceCameraSettings' => $faceCameraSettings,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'image_base64' => ['required', 'string'],
            'face_descriptor' => ['required'],
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);
        if ($this->hasRegisteredFace($user)) {
            return response()->json([
                'status' => 'face_already_registered',
                'message' => sprintf('Data wajah untuk %s (%s) sudah tersimpan. Hapus data wajah terlebih dahulu sebelum registrasi ulang.', $user->name, $user->kelas),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'identity_number' => $user->identity_number,
                    'kelas' => $user->kelas,
                    'face_registered_at' => optional($user->face_registered_at)->toDateTimeString(),
                ],
            ], 409);
        }

        $faceDescriptor = $this->faceDescriptorService->normalize($validated['face_descriptor']);

        if ($faceDescriptor === null) {
            return response()->json([
                'status' => 'invalid_descriptor',
                'message' => 'Descriptor wajah tidak valid. Silakan ulangi proses scan.',
            ], 422);
        }

        $matchingUser = $this->faceEncodingMatcher->findMatchingUserByEncoding($faceDescriptor, $user->id);

        if ($matchingUser !== null) {
            return response()->json([
                'status' => 'duplicate_face',
                'message' => sprintf('Wajah ini sudah terdaftar pada akun %s (%s). Hapus data wajah pengguna tersebut terlebih dahulu sebelum registrasi baru.', $matchingUser->name, $matchingUser->kelas),
                'user' => [
                    'id' => $matchingUser->id,
                    'name' => $matchingUser->name,
                    'identity_number' => $matchingUser->identity_number,
                    'kelas' => $matchingUser->kelas,
                ],
            ], 409);
        }

        $previousThumbnailPath = $user->face_thumbnail_path;
        $thumbnailPath = $this->storeFaceThumbnail($user, (string) $validated['image_base64']);

        $user->update([
            'face_encoding' => json_encode(array_values($faceDescriptor)),
            'face_registered_at' => now(),
            'face_thumbnail_path' => $thumbnailPath ?? $previousThumbnailPath,
        ]);

        if ($thumbnailPath && filled($previousThumbnailPath) && $previousThumbnailPath !== $thumbnailPath) {
            Storage::disk('public')->delete($previousThumbnailPath);
        }

        return response()->json([
            'status' => 'ok',
            'message' => sprintf('Registrasi wajah berhasil disimpan untuk %s (%s).', $user->name, $user->kelas),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'identity_number' => $user->identity_number,
                'kelas' => $user->kelas,
                'face_registered_at' => optional($user->face_registered_at)->toDateTimeString(),
                'face_thumbnail_path' => $user->face_thumbnail_path,
            ],
        ]);
    }

    public function recognize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'face_descriptor' => ['required'],
        ]);

        $faceUsers = User::query()
            ->whereNotNull('face_encoding')
            ->where('face_encoding', '!=', '')
            ->orderBy('id')
            ->get(['id', 'name', 'identity_number', 'kelas', 'face_encoding']);

        if ($faceUsers->isEmpty()) {
            return response()->json([
                'status' => 'empty_reference',
                'recognized' => false,
                'message' => 'Belum ada data wajah terdaftar.',
            ], 404);
        }

        $faceDescriptor = $this->faceDescriptorService->normalize($validated['face_descriptor']);

        if ($faceDescriptor === null) {
            return response()->json([
                'status' => 'invalid_descriptor',
                'recognized' => false,
                'message' => 'Descriptor wajah tidak valid.',
            ], 422);
        }

        $matchedUser = $this->faceEncodingMatcher->findMatchingUserByEncoding($faceDescriptor);

        if (!$matchedUser) {
            return response()->json([
                'status' => 'unknown',
                'recognized' => false,
                'message' => 'Wajah tidak dikenali.',
            ]);
        }

        $storedEncoding = json_decode((string) $matchedUser->face_encoding, true);
        $distance = is_array($storedEncoding)
            ? $this->faceEncodingMatcher->calculateDistance($faceDescriptor, array_values($storedEncoding))
            : null;

        return response()->json([
            'status' => 'matched',
            'recognized' => true,
            'message' => 'Wajah berhasil dikenali.',
            'confidence_score' => $distance !== null ? max(0.0, 1.0 - $distance) : null,
            'user' => [
                'id' => $matchedUser->id,
                'name' => $matchedUser->name,
                'identity_number' => $matchedUser->identity_number,
                'kelas' => $matchedUser->kelas,
            ],
        ]);
    }

    private function hasRegisteredFace(User $user): bool
    {
        return $this->hasFaceEncoding($user->face_encoding) || filled($user->face_thumbnail_path);
    }

    private function hasFaceEncoding(?string $faceEncoding): bool
    {
        if ($faceEncoding === null) {
            return false;
        }

        $normalizedEncoding = trim($faceEncoding);

        return $normalizedEncoding !== '' && $normalizedEncoding !== '[]';
    }

    private function storeFaceThumbnail(User $user, string $imageBase64): ?string
    {
        try {
            $imageBytes = $this->extractImageBytes($imageBase64);
            $sourceImage = imagecreatefromstring($imageBytes);

            if ($sourceImage === false) {
                return null;
            }

            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);

            if ($sourceWidth <= 0 || $sourceHeight <= 0) {
                imagedestroy($sourceImage);

                return null;
            }

            $targetWidth = min(320, $sourceWidth);
            $targetHeight = (int) max(1, round($sourceHeight * ($targetWidth / $sourceWidth)));
            $thumbnailImage = imagecreatetruecolor($targetWidth, $targetHeight);

            if ($thumbnailImage === false) {
                imagedestroy($sourceImage);

                return null;
            }

            imagecopyresampled(
                $thumbnailImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $sourceWidth,
                $sourceHeight,
            );

            ob_start();
            imagejpeg($thumbnailImage, null, 82);
            $jpegData = ob_get_clean();

            imagedestroy($sourceImage);
            imagedestroy($thumbnailImage);

            if (!is_string($jpegData) || $jpegData === '') {
                return null;
            }

            $thumbnailPath = sprintf('face-thumbnails/user-%d-%s.jpg', $user->id, now()->format('Ymd_His'));
            Storage::disk('public')->put($thumbnailPath, $jpegData);

            return $thumbnailPath;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * @return array<string, int|string>
     */
    private function resolveFaceCameraSettings(): array
    {
        $defaults = [
            'face_camera_preview_size' => '420',
            'face_camera_capture_size' => '512',
            'face_camera_border_radius' => '16',
            'face_camera_background' => '#111111',
            'face_camera_object_fit' => 'cover',
            'face_camera_frame_mode' => 'square',
            'face_camera_horizontal_shift' => '0',
            'face_camera_vertical_shift' => '0',
        ];

        $storedSettingValues = Setting::query()
            ->whereIn('setting_key', array_keys($defaults))
            ->pluck('setting_value', 'setting_key');

        $settings = $defaults;

        foreach ($storedSettingValues as $key => $value) {
            if (!is_string($key) || !array_key_exists($key, $settings)) {
                continue;
            }

            if ($value !== null && trim((string) $value) !== '') {
                $settings[$key] = (string) $value;
            }
        }

        $previewSize = max(280, min(720, (int) $settings['face_camera_preview_size']));
        $captureSize = max(320, min(1024, (int) $settings['face_camera_capture_size']));
        $borderRadius = max(0, min(32, (int) $settings['face_camera_border_radius']));
        $background = strtolower((string) $settings['face_camera_background']);
        $objectFit = in_array($settings['face_camera_object_fit'], ['cover', 'contain'], true)
            ? (string) $settings['face_camera_object_fit']
            : 'cover';
        $frameMode = in_array($settings['face_camera_frame_mode'], ['square', 'wide'], true)
            ? (string) $settings['face_camera_frame_mode']
            : 'square';
        $horizontalShift = max(-100, min(100, (int) $settings['face_camera_horizontal_shift']));
        $verticalShift = max(-100, min(100, (int) $settings['face_camera_vertical_shift']));

        if (!preg_match('/^#[0-9a-f]{6}$/', $background)) {
            $background = '#111111';
        }

        return [
            'face_camera_preview_size' => $previewSize,
            'face_camera_capture_size' => $captureSize,
            'face_camera_border_radius' => $borderRadius,
            'face_camera_background' => $background,
            'face_camera_object_fit' => $objectFit,
            'face_camera_frame_mode' => $frameMode,
            'face_camera_frame_ratio' => $frameMode === 'wide' ? '4 / 3' : '1 / 1',
            'face_camera_horizontal_shift' => $horizontalShift,
            'face_camera_vertical_shift' => $verticalShift,
        ];
    }

    private function extractImageBytes(string $imageBase64): string
    {
        if (!is_string($imageBase64) || trim($imageBase64) === '') {
            throw new RuntimeException('image_base64 wajib diisi.');
        }

        $payload = trim($imageBase64);

        if (str_contains($payload, ',')) {
            $payload = explode(',', $payload, 2)[1];
        }

        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            throw new RuntimeException('image_base64 tidak valid.');
        }

        return $decoded;
    }
}
