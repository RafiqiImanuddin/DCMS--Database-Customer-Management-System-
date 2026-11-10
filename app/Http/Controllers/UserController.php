<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan formulir registrasi akun baru (Guest).
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Simpan pendaftaran user baru (Default Role: 'user' / Staff).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau masuk dengan akun tersebut.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => 'user', // Akses default: Customer & Outreach, Workspace Dashboard & Matrix, Guidance SOP, Profile Setting
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('show_login_guidance', true);

        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil! Selamat datang di DCMS Gadgetmart, ' . $user->name . '.');
    }

    /**
     * Tampilkan halaman kelola akun dan konfigurasi checklist izin fitur (Khusus Superadmin).
     */
    public function manageUsers(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang berwenang mengelola akun dan checklist hak akses fitur.');

        $search = $request->string('search')->trim()->toString();
        $roleFilter = $request->string('role')->trim()->toString();

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== '' && in_array($roleFilter, ['superadmin', 'admin', 'user'], true)) {
            $query->where('role', $roleFilter);
        }

        $users = $query->orderByRaw("CASE WHEN role = 'superadmin' THEN 1 WHEN role = 'admin' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $totalUsers = User::count();
        $superadminCount = User::where('role', 'superadmin')->count();
        $adminCount = User::where('role', 'admin')->count();
        $staffCount = User::where(function ($q) {
            $q->where('role', 'user')->orWhereNull('role');
        })->count();

        $availableFeatures = User::availableFeatures();
        $groupedFeatures = User::groupedFeatures();

        return view('settings.users', compact(
            'users',
            'search',
            'roleFilter',
            'totalUsers',
            'superadminCount',
            'adminCount',
            'staffCount',
            'availableFeatures',
            'groupedFeatures'
        ));
    }

    /**
     * Tambahkan akun user baru langsung dari panel Superadmin.
     */
    public function storeByAdmin(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Akses terbatas.');

        $featureKeys = array_keys(User::availableFeatures());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:admin,user'],
            'password' => ['required', 'string', 'min:6'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', $featureKeys)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar pada akun lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $role = $validated['role'];
        $permissions = isset($validated['permissions'])
            ? array_values($validated['permissions'])
            : User::defaultPermissionsForRole($role);

        User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'permissions' => $permissions,
        ]);

        return back()->with('success', 'Akun ' . $validated['name'] . ' berhasil ditambahkan beserta izin checklist fiturnya.');
    }

    /**
     * Update checklist hak akses fitur dan/atau role untuk user tertentu (Khusus Superadmin).
     */
    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Akses terbatas.');

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Akun Superadmin memiliki akses penuh permanen ke seluruh fitur dan tidak dapat dibatasi.');
        }

        $featureKeys = array_keys(User::availableFeatures());

        $action = $request->input('action', 'save');

        if ($action === 'reset_default') {
            $user->permissions = null;
            $user->save();

            return back()->with('success', 'Checklist fitur untuk ' . $user->name . ' telah di-reset ke izin default sesuai perannya (' . strtoupper($user->role) . ').');
        }

        $validated = $request->validate([
            'role' => ['sometimes', 'required', 'string', 'in:admin,user'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', $featureKeys)],
        ]);

        if (isset($validated['role'])) {
            $user->role = $validated['role'];
        }

        $user->permissions = array_values($validated['permissions'] ?? []);
        $user->save();

        try {
            $actor = $request->user();
            $actorName = $actor ? $actor->name : 'Superadmin';
            $activeCount = count($user->permissions);
            \App\Models\ActivityNotification::log(
                title: 'Pembaruan Hak Akses Fitur',
                message: "{$actorName} memperbarui checklist izin fitur untuk akun {$user->name} ({$activeCount} fitur aktif)",
                user: $actor,
                type: 'info',
                action: 'update_permissions',
                icon: 'shield'
            );
        } catch (\Throwable $e) {}

        return back()->with('success', 'Checklist hak akses fitur untuk ' . $user->name . ' berhasil diperbarui (' . count($user->permissions) . ' fitur aktif).');
    }

    /**
     * Hapus akun pengguna (Superadmin tidak bisa dihapus dan tidak bisa hapus diri sendiri).
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Akses terbatas.');

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Akun dengan role Superadmin tidak dapat dihapus.');
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', 'Akun "' . $userName . '" berhasil dihapus dari sistem.');
    }

    /**
     * Tampilkan halaman pengaturan profil / akun sendiri.
     */
    public function profile(): View
    {
        $user = Auth::user();

        return view('settings.profile', compact('user'));
    }

    /**
     * Update profil nama dan email akun sendiri.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        $user->name = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));
        $user->save();

        return back()->with('success', 'Profil akun Anda berhasil diperbarui.');
    }

    /**
     * Update kata sandi akun sendiri.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini yang Anda masukkan tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('success', 'Kata sandi akun Anda berhasil diperbarui.');
    }
}
