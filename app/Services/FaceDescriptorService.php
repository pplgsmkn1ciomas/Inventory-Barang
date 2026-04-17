<?php

namespace App\Services;

class FaceDescriptorService
{
    /**
     * @param mixed $descriptor
     * @return array<int, float>|null
     */
    public function normalize(mixed $descriptor): ?array
    {
        if (is_string($descriptor)) {
            $decoded = json_decode(trim($descriptor), true);

            if (!is_array($decoded)) {
                return null;
            }

            $descriptor = $decoded;
        }

        if (!is_array($descriptor)) {
            return null;
        }

        $values = array_values($descriptor);

        if (count($values) !== 128) {
            return null;
        }

        $normalized = [];

        foreach ($values as $value) {
            if (!is_numeric($value)) {
                return null;
            }

            $normalized[] = (float) $value;
        }

        return $normalized;
    }
}