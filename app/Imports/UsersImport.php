<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class UsersImport implements SkipsEmptyRows, ToCollection, WithCalculatedFormulas, WithHeadingRow
{
    private int $createdCount = 0;

    private int $updatedCount = 0;

    private int $skippedCount = 0;

    /**
     * @var array<string, string>
     */
    private array $allowedRoleLookup = [];

    /**
     * @var array<string, string>
     */
    private array $allowedClassLookup = [];

    /**
     * @param list<string> $allowedRoles
     * @param list<string> $allowedClasses
     */
    public function __construct(array $allowedRoles = ['admin', 'teacher', 'student'], array $allowedClasses = ['-'])
    {
        $this->setAllowedRoles($allowedRoles);
        $this->setAllowedClasses($allowedClasses);
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $rowData = $row instanceof Collection ? $row : collect($row);

            $identityNumber = $this->readValue($rowData, ['identity', 'identity_number']);
            $name = $this->readValue($rowData, ['nama', 'name']);
            $kelas = $this->normalizeClass($this->readValue($rowData, ['kelas']));
            $phone = $this->readValue($rowData, ['no_hp', 'phone', 'nohp']);
            $role = $this->normalizeRole($this->readValue($rowData, ['role']));

            if ($this->isEmptyRow([$identityNumber, $name, $kelas, $phone, $role])) {
                $this->skippedCount++;
                continue;
            }

            if ($identityNumber === '' || $name === '' || $kelas === '' || $phone === '' || $role === '') {
                $this->skippedCount++;
                continue;
            }

            $payload = [
                'name' => Str::limit($name, 120, ''),
                'role' => $role,
                'kelas' => Str::limit($kelas, 120, ''),
                'phone' => Str::limit($phone, 30, ''),
                'is_active' => true,
            ];

            try {
                $existingUser = User::query()->where('identity_number', $identityNumber)->first();

                if ($existingUser !== null) {
                    $existingUser->update($payload);
                    $this->updatedCount++;
                    continue;
                }

                User::query()->create([
                    'identity_number' => Str::limit($identityNumber, 120, ''),
                    'email' => null,
                    'password' => null,
                    ...$payload,
                ]);

                $this->createdCount++;
            } catch (Throwable) {
                $this->skippedCount++;
            }
        }
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * @param Collection<int, mixed> $row
     * @param list<string> $keys
     */
    private function readValue(Collection $row, array $keys): string
    {
        foreach ($keys as $key) {
            $raw = $row->get($key);

            if ($raw === null) {
                continue;
            }

            $clean = trim((string) $raw);

            if ($clean === '') {
                continue;
            }

            return $clean;
        }

        return '';
    }

    /**
     * @param list<string> $values
     */
    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim($value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeRole(string $value): string
    {
        $normalized = Str::lower(trim($value));

        return $this->allowedRoleLookup[$normalized] ?? '';
    }

    /**
     * @param list<string> $allowedRoles
     */
    private function setAllowedRoles(array $allowedRoles): void
    {
        $lookup = [];

        foreach ($allowedRoles as $role) {
            $cleanRole = trim((string) $role);

            if ($cleanRole === '') {
                continue;
            }

            $lookup[Str::lower($cleanRole)] = Str::limit($cleanRole, 120, '');
        }

        if ($lookup === []) {
            $lookup = [
                'admin' => 'admin',
                'teacher' => 'teacher',
                'student' => 'student',
            ];
        }

        $this->allowedRoleLookup = $lookup;
    }

    private function normalizeClass(string $value): string
    {
        $normalized = Str::lower(trim($value));

        return $this->allowedClassLookup[$normalized] ?? '';
    }

    /**
     * @param list<string> $allowedClasses
     */
    private function setAllowedClasses(array $allowedClasses): void
    {
        $lookup = [];

        foreach ($allowedClasses as $class) {
            $cleanClass = trim((string) $class);

            if ($cleanClass === '') {
                continue;
            }

            $lookup[Str::lower($cleanClass)] = Str::limit($cleanClass, 120, '');
        }

        if ($lookup === []) {
            $lookup = [
                '-' => '-',
            ];
        }

        $this->allowedClassLookup = $lookup;
    }
}
