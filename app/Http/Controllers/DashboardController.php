<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function showAdminLogin(): RedirectResponse
    {
        return redirect()
            ->route('dashboard.public')
            ->with('show_admin_login', true);
    }

    public function publicIndex(): View
    {
        $availableAssets = Asset::query()
            ->where('status', 'available')
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        $activeLoans = Loan::query()
            ->with([
                'user:id,name,identity_number,kelas,phone,role',
                'asset:id,brand,model',
            ])
            ->whereIn('status', ['active', 'overdue'])
            ->latest('loan_date')
            ->get()
            ->filter(fn (Loan $loan) => strtolower((string) ($loan->user?->role ?? 'student')) !== 'teacher')
            ->values();

        return view('dashboard.public', [
            'availableAssets' => $availableAssets,
            'activeLoans' => $activeLoans,
        ]);
    }

    public function loginAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $admin = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->get(['id', 'name', 'password'])
            ->first(function (User $user) use ($validated): bool {
                return filled($user->password) && Hash::check($validated['password'], $user->password);
            });

        if (!$admin) {
            return redirect()
                ->route('dashboard.public')
                ->with('error', 'Password admin tidak valid.')
                ->with('show_admin_login', true);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_access', [
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'granted_at' => now()->getTimestamp(),
        ]);

        return redirect()
            ->route('dashboard.admin')
            ->with('success', 'Login admin berhasil.');
    }

    public function logoutAdmin(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_access');
        $request->session()->regenerateToken();

        return redirect()
            ->route('dashboard.public')
            ->with('success', 'Logout admin berhasil.');
    }

    public function adminIndex(): View
    {
        $assets = Asset::query()->get();
        $activeLoans = Loan::query()
            ->with([
                'user:id,name,identity_number,kelas,phone',
                'asset:id,brand,model,serial_number',
            ])
            ->where('status', 'active')
            ->latest('loan_date')
            ->get();

        $normalizeCategory = static function (?string $category): string {
            $value = trim((string) $category);

            return $value !== '' ? $value : 'Tanpa Kategori';
        };

        $isLaptopCategory = static function (?string $category): bool {
            $value = strtolower(trim((string) $category));

            return str_contains($value, 'laptop') || str_contains($value, 'notebook');
        };

        $toSlides = static function ($items, callable $groupBy): Collection {
            return $items
                ->groupBy($groupBy)
                ->map(fn ($group, $label) => [
                    'label' => (string) $label,
                    'count' => $group->count(),
                ])
                ->sortByDesc('count')
                ->values();
        };

        $totalCategorySlides = $toSlides(
            $assets->reject(fn (Asset $asset) => $isLaptopCategory($asset->category)),
            fn (Asset $asset) => $normalizeCategory($asset->category)
        );

        $laptopAssets = $assets->filter(
            fn (Asset $asset) => $isLaptopCategory($asset->category)
        );

        $totalLaptopBrandSlides = $toSlides(
            $laptopAssets,
            fn (Asset $asset) => ($brand = trim((string) $asset->brand)) !== '' ? $brand : 'Tanpa Merek'
        );

        $availableLaptopAssets = $laptopAssets
            ->where('status', 'available')
            ->sortBy(fn (Asset $asset) => strtolower(trim((string) $asset->brand . ' ' . (string) $asset->model)))
            ->values();

        $availableLaptopBrandSlides = $toSlides(
            $availableLaptopAssets,
            fn (Asset $asset) => ($brand = trim((string) $asset->brand)) !== '' ? $brand : 'Tanpa Merek'
        );

        $borrowedCategorySlides = $toSlides(
            $activeLoans
                ->filter(fn (Loan $loan) => $loan->asset !== null)
                ->map(fn (Loan $loan) => $loan->asset),
            fn (Asset $asset) => $normalizeCategory($asset->category)
        );

        $damagedAssets = $assets->filter(function (Asset $asset): bool {
            return in_array((string) $asset->condition, ['minor_damage', 'major_damage'], true)
                || $asset->status === 'maintenance';
        });

        $damagedCategorySlides = $toSlides(
            $damagedAssets,
            fn (Asset $asset) => $normalizeCategory($asset->category)
        );

        return view('dashboard.admin', [
            'totalAssets' => $assets->count(),
            'totalLaptopAssets' => $laptopAssets->count(),
            'availableLaptopAssetsCount' => $availableLaptopAssets->count(),
            'borrowedAssets' => $activeLoans->count(),
            'damagedAssets' => $damagedAssets->count(),
            'totalCategorySlides' => $totalCategorySlides,
            'totalLaptopBrandSlides' => $totalLaptopBrandSlides,
            'availableLaptopBrandSlides' => $availableLaptopBrandSlides,
            'borrowedCategorySlides' => $borrowedCategorySlides,
            'damagedCategorySlides' => $damagedCategorySlides,
            'activeLoans' => $activeLoans,
            'availableAssets' => $availableLaptopAssets,
        ]);
    }
}
