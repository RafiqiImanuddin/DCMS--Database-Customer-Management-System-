<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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
     * Langkah 1: Validasi form pendaftaran dan kirimkan 6-digit kode OTP verifikasi.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
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

        // Generate 6-digit secure numeric OTP
        $otp = (string) random_int(100000, 999999);

        $pending = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
            'password_hash' => Hash::make($validated['password']),
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ];

        $request->session()->put('pending_registration', $pending);

        // Kirimkan OTP via Email & WhatsApp
        $this->dispatchRegistrationOtp($pending['name'], $pending['email'], $pending['phone'], $otp);

        // Flash kode OTP demo untuk kemudahan pengujian di local / log mailer
        if (app()->environment('local') || config('mail.default') === 'log') {
            $request->session()->flash('demo_otp', $otp);
        }

        return redirect()->route('register.otp.form')->with('info', "Kode verifikasi OTP 6 digit telah dikirimkan ke {$pending['email']}. Silakan masukkan kode untuk memverifikasi akun Anda.");
    }

    /**
     * Tampilkan halaman verifikasi OTP pendaftaran akun baru.
     */
    public function showOtpForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $pending = $request->session()->get('pending_registration');
        if (!$pending || !is_array($pending)) {
            return redirect()->route('register')->with('error', 'Silakan isi formulir pendaftaran terlebih dahulu.');
        }

        $email = $pending['email'] ?? '';
        $phone = $pending['phone'] ?? null;
        $expiresAt = $pending['expires_at'] ?? now()->addMinutes(10)->timestamp;

        return view('auth.verify-otp', compact('email', 'phone', 'expiresAt'));
    }

    /**
     * Langkah 2: Verifikasi kode OTP dan selesaikan pembuatan akun baru.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('pending_registration');
        if (!$pending || !is_array($pending)) {
            return redirect()->route('register')->with('error', 'Sesi pendaftaran Anda telah berakhir. Silakan lakukan pendaftaran ulang.');
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus terdiri dari 6 digit angka.',
        ]);

        $inputOtp = trim((string) $request->input('otp'));

        // Cek kedaluwarsa (10 menit)
        if (($pending['expires_at'] ?? 0) < now()->timestamp) {
            return back()->with('error', 'Kode OTP telah kedaluwarsa. Silakan klik "Kirim Ulang Kode OTP" di bawah.');
        }

        // Cek batas percobaan salah (maksimal 5 kali)
        if (($pending['attempts'] ?? 0) >= 5) {
            $request->session()->forget('pending_registration');
            return redirect()->route('register')->with('error', 'Terlalu banyak percobaan kode OTP yang salah. Demi keamanan data internal, pendaftaran dibatalkan. Silakan daftar ulang.');
        }

        // Cocokkan OTP
        if ($inputOtp !== (string) $pending['otp']) {
            $pending['attempts'] = ($pending['attempts'] ?? 0) + 1;
            $request->session()->put('pending_registration', $pending);
            $sisa = 5 - $pending['attempts'];

            return back()->with('error', "Kode OTP tidak valid atau salah. Sisa kesempatan verifikasi: {$sisa} kali.")->withInput();
        }

        // OTP Valid: Simpan User ke Database dengan email_verified_at
        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'phone' => $pending['phone'] ?? null,
            'password' => $pending['password_hash'],
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Bersihkan sesi pendaftaran
        $request->session()->forget('pending_registration');

        // Login otomatis user
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->flash('show_login_guidance', true);

        return redirect()->route('dashboard')->with('success', "Akun Anda berhasil diverifikasi menggunakan OTP! Selamat datang di DCMS Gadgetmart, {$user->name}.");
    }

    /**
     * Kirim ulang kode OTP pendaftaran baru dengan proteksi cooldown 60 detik.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('pending_registration');
        if (!$pending || !is_array($pending)) {
            return redirect()->route('register')->with('error', 'Sesi pendaftaran Anda telah berakhir. Silakan lakukan pendaftaran ulang.');
        }

        $lastSent = $pending['last_sent_at'] ?? 0;
        $cooldown = 60;
        $elapsed = now()->timestamp - $lastSent;

        if ($elapsed < $cooldown) {
            $remaining = $cooldown - $elapsed;
            return back()->with('error', "Mohon tunggu {$remaining} detik sebelum meminta kode OTP baru.");
        }

        // Generate OTP baru
        $otp = (string) random_int(100000, 999999);
        $pending['otp'] = $otp;
        $pending['expires_at'] = now()->addMinutes(10)->timestamp;
        $pending['attempts'] = 0;
        $pending['last_sent_at'] = now()->timestamp;
        $request->session()->put('pending_registration', $pending);

        // Kirimkan
        $this->dispatchRegistrationOtp($pending['name'], $pending['email'], $pending['phone'], $otp);

        if (app()->environment('local') || config('mail.default') === 'log') {
            $request->session()->flash('demo_otp', $otp);
        }

        return back()->with('success', 'Kode OTP baru telah berhasil dikirimkan ke email Anda.');
    }

    /**
     * Batalkan proses pendaftaran saat ini dan kembali ke form awal.
     */
    public function cancelRegistration(Request $request): RedirectResponse
    {
        $request->session()->forget('pending_registration');
        return redirect()->route('register');
    }

    /**
     * Kirimkan OTP melalui Email dan WhatsApp (jika nomor terdaftar).
     */
    protected function dispatchRegistrationOtp(string $name, string $email, ?string $phone, string $otp): void
    {
        // 1. Kirim via Laravel Mail
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Halo {$name},\n\nKode verifikasi OTP pendaftaran akun DCMS Gadgetmart Anda adalah:\n\n* {$otp} *\n\nKode ini berlaku selama 10 menit. Demi keamanan data internal perusahaan, jangan bagikan kode OTP ini kepada siapapun.\n\nSalam,\nTim Keamanan DCMS Gadgetmart",
                function ($message) use ($email, $name, $otp) {
                    $message->to($email, $name)
                        ->subject("Kode Verifikasi OTP DCMS Gadgetmart: {$otp}");
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim email OTP ke {$email}: " . $e->getMessage());
        }

        // 2. Kirim via WhatsApp jika nomor handphone tersedia
        if (!empty($phone)) {
            try {
                $watzap = app(\App\Services\WatzapService::class);
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
                if (!empty($cleanPhone)) {
                    $waMessage = "🔐 *VERIFIKASI OTP DCMS GADGETMART*\n\nHalo *{$name}*,\n\nKode OTP pendaftaran akun Anda adalah:\n👉 *{$otp}*\n\nKode berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun demi keamanan data internal.";
                    $watzap->sendMessage($cleanPhone, $waMessage);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal mengirim WhatsApp OTP ke {$phone}: " . $e->getMessage());
            }
        }

        \Illuminate\Support\Facades\Log::info("DCMS Security OTP Registration: email={$email}, phone={$phone}, otp={$otp}");
    }

    /**
     * Tampilkan halaman kelola akun dan konfigurasi checklist izin fitur (Khusus Superadmin).
     */
    public function manageUsers(Request $request): View|\Illuminate\Http\JsonResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang berwenang mengelola akun dan checklist hak akses fitur.');

        $search = $request->string('search')->trim()->toString();
        $roleFilter = $request->string('role')->trim()->toString();
        $statusFilter = $request->string('status')->trim()->toString();

        // Ambil data sesi aktif terakhir per user dari tabel sessions (dalam detik unix)
        $onlineThreshold = now()->subMinutes(5)->timestamp;
        $userLastActivities = \Illuminate\Support\Facades\DB::table('sessions')
            ->whereNotNull('user_id')
            ->select('user_id', \Illuminate\Support\Facades\DB::raw('MAX(last_activity) as last_activity'))
            ->groupBy('user_id')
            ->pluck('last_activity', 'user_id')
            ->toArray();

        $onlineUserIds = [];
        foreach ($userLastActivities as $userId => $lastAct) {
            if ($lastAct >= $onlineThreshold) {
                $onlineUserIds[] = (int) $userId;
            }
        }
        $onlineUsersCount = count($onlineUserIds);

        // Jika dipanggil via AJAX untuk live polling status online/offline
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'onlineUsersCount' => $onlineUsersCount,
                'userLastActivities' => $userLastActivities,
                'onlineThreshold' => $onlineThreshold,
            ]);
        }

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== '' && in_array($roleFilter, ['superadmin', 'admin', 'user'], true)) {
            $query->where('role', $roleFilter);
        }

        if ($statusFilter === 'online') {
            $query->whereIn('id', $onlineUserIds);
        } elseif ($statusFilter === 'offline') {
            $query->whereNotIn('id', $onlineUserIds);
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
            'statusFilter',
            'totalUsers',
            'superadminCount',
            'adminCount',
            'staffCount',
            'onlineUsersCount',
            'userLastActivities',
            'onlineThreshold',
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
            'phone' => ['nullable', 'string', 'max:25'],
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

        $userData = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ];
        if (Schema::hasColumn('users', 'phone') && ! empty($validated['phone'])) {
            $userData['phone'] = trim($validated['phone']);
        }
        if (Schema::hasColumn('users', 'permissions')) {
            $userData['permissions'] = $permissions;
        }

        User::create($userData);

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

        if (Schema::hasColumn('users', 'permissions')) {
            $user->permissions = array_values($validated['permissions'] ?? []);
        }
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
