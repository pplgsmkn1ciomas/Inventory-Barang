<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('identity_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('kelas', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->string('kelas'));
        }

        $users = $query->latest('id')->paginate(20)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'kelasOptions' => User::query()->select('kelas')->distinct()->orderBy('kelas')->pluck('kelas'),
            'filters' => [
                'search' => (string) $request->input('search', ''),
                'role' => (string) $request->input('role', ''),
                'kelas' => (string) $request->input('kelas', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'identity_number' => ['required', 'string', 'max:120', 'unique:users,identity_number'],
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
            'kelas' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            $validated['password'] = null;
        }

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'identity_number' => ['required', 'string', 'max:120', Rule::unique('users', 'identity_number')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
            'kelas' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->loans()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Pengguna tidak bisa dihapus karena memiliki riwayat peminjaman.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
