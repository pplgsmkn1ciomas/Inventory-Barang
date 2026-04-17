<?php

namespace App\Services;

use App\Models\User;

class FaceEncodingMatcher
{
    public function findMatchingUserByEncoding(array $candidateEncoding, ?int $excludeUserId = null): ?User
    {
        $candidateValues = array_values($candidateEncoding);

        if (count($candidateValues) !== 128) {
            return null;
        }

        $tolerance = (float) config('services.face_recognition.tolerance', 0.45);
        $matchingUser = null;
        $bestDistance = null;

        $faceUsers = User::query()
            ->whereNotNull('face_encoding')
            ->where('face_encoding', '!=', '')
            ->when($excludeUserId !== null, static function ($query) use ($excludeUserId): void {
                $query->where('id', '!=', $excludeUserId);
            })
            ->get(['id', 'name', 'identity_number', 'kelas', 'face_encoding']);

        foreach ($faceUsers as $faceUser) {
            $storedEncoding = json_decode((string) $faceUser->face_encoding, true);

            if (!is_array($storedEncoding)) {
                continue;
            }

            $storedValues = array_values($storedEncoding);

            if (count($storedValues) !== 128) {
                continue;
            }

            $distance = $this->calculateDistance($candidateValues, $storedValues);

            if ($distance > $tolerance) {
                continue;
            }

            if ($bestDistance === null || $distance < $bestDistance) {
                $bestDistance = $distance;
                $matchingUser = $faceUser;
            }
        }

        return $matchingUser;
    }

    /**
     * @param array<int, float|int|string> $firstEncoding
     * @param array<int, float|int|string> $secondEncoding
     */
    public function calculateDistance(array $firstEncoding, array $secondEncoding): float
    {
        $sum = 0.0;

        foreach ($firstEncoding as $index => $value) {
            $difference = (float) $value - (float) ($secondEncoding[$index] ?? 0.0);
            $sum += $difference * $difference;
        }

        return sqrt($sum);
    }

}