<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\Loan;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LoanController extends Controller
{
    public function __construct(private readonly LoanService $loanService)
    {
    }

    public function index()
    {
        return view('admin.barcode.index', [
            'assets' => Asset::query()
                ->orderBy('category')
                ->orderBy('brand')
                ->orderBy('model')
                ->get(['id', 'category', 'brand', 'model', 'serial_number', 'barcode', 'status', 'condition']),
        ]);
    }

    public function borrow(Request $request)
    {
        $validated = $request->validate([
            'identity_number' => ['required', 'string', 'exists:users,identity_number'],
            'asset_code' => ['required', 'string'],
        ]);

        $user = User::query()->where('identity_number', $validated['identity_number'])->firstOrFail();
        $asset = Asset::query()
            ->where('serial_number', $validated['asset_code'])
            ->orWhere('barcode', $validated['asset_code'])
            ->first();

        if (!$asset) {
            return redirect()->back()->with('error', 'Asset tidak ditemukan dari serial number/barcode tersebut.');
        }

        try {
            $loan = $this->loanService->createLoan($user, $asset);

            $this->logAction('BORROW', 'loans', sprintf(
                '%s meminjam %s %s',
                $user->name,
                $asset->brand,
                $asset->model
            ), 'Due date: ' . ($loan->due_date?->format('Y-m-d H:i:s') ?? '-'));

            return redirect()->back()->with('success', 'Peminjaman berhasil dicatat.');
        } catch (RuntimeException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    public function returnItem(Request $request)
    {
        $validated = $request->validate([
            'identity_number' => ['required', 'string', 'exists:users,identity_number'],
            'asset_code' => ['required', 'string'],
            'condition' => ['required', 'in:good,minor_damage,major_damage'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = User::query()->where('identity_number', $validated['identity_number'])->firstOrFail();
        $asset = Asset::query()
            ->where('serial_number', $validated['asset_code'])
            ->orWhere('barcode', $validated['asset_code'])
            ->first();

        if (!$asset) {
            return redirect()->back()->with('error', 'Asset tidak ditemukan dari serial number/barcode tersebut.');
        }

        $loan = Loan::query()
            ->where('user_id', $user->id)
            ->where('asset_id', $asset->id)
            ->whereIn('status', ['active', 'overdue'])
            ->latest('loan_date')
            ->first();

        if (!$loan) {
            return redirect()->back()->with('error', 'Tidak ada pinjaman aktif untuk user dan asset ini.');
        }

        try {
            $this->loanService->returnLoan(
                $loan,
                $validated['condition'],
                $validated['notes'] ?? null
            );

            $this->logAction('RETURN', 'loans', sprintf(
                '%s mengembalikan %s %s',
                $user->name,
                $asset->brand,
                $asset->model
            ), 'Condition: ' . $validated['condition']);

            return redirect()->back()->with('success', 'Pengembalian berhasil diproses.');
        } catch (RuntimeException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    private function logAction(string $action, string $tableName, string $data, string $details = ''): void
    {
        DB::transaction(function () use ($action, $tableName, $data, $details): void {
            ActivityLog::query()->create([
                'timestamp' => now(),
                'action' => $action,
                'table_name' => $tableName,
                'data' => $data,
                'details' => $details,
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
