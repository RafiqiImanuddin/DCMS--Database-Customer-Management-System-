<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        $request->session()->flash('show_login_guidance', true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Otentikasi instan sebagai akun Tamu (Guest) dengan akses Hanya-Lihat (Read-Only).
     */
    public function loginAsGuest(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $guest = \App\Models\User::firstOrCreate(
            ['email' => 'guest@gadgetmart.id'],
            [
                'name' => 'Tamu (Guest)',
                'phone' => '080000000000',
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                'role' => 'guest',
                'email_verified_at' => now(),
            ]
        );

        if ($guest->role !== 'guest') {
            $guest->update(['role' => 'guest']);
        }

        Auth::login($guest);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('info', 'Anda masuk sebagai Tamu (Guest). Mode Hanya-Lihat (Read-Only) aktif dan data sensitif disensor.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
