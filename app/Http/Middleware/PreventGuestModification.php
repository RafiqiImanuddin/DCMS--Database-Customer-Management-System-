<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventGuestModification
{
    /**
     * Handle an incoming request.
     * Mencegah akun Tamu (Guest) melakukan modifikasi data (POST, PUT, PATCH, DELETE).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isGuest()) {
            // Metode HTTP yang bersifat hanya baca (read-only) selalu diizinkan
            if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
                return $next($request);
            }

            // Aksi khusus yang diizinkan untuk guest (misal: logout dan dismiss panduan)
            if ($request->routeIs('logout', 'guidance.dismiss')) {
                return $next($request);
            }

            // Blokir seluruh aksi create, update, delete, upload, assign, reset, blast
            $message = 'Akses ditolak: Akun Tamu (Guest) berada dalam mode Hanya-Lihat (Read-Only) dan tidak dapat menambah, mengubah, mengunggah, atau menghapus data.';

            if ($request->expectsJson() || $request->is('api/*') || $request->is('settings/watzap/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'READ_ONLY_GUEST',
                    'message' => $message,
                ], 403);
            }

            return back()->with('error', $message);
        }

        return $next($request);
    }
}
