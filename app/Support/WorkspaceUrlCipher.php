<?php

namespace App\Support;

use Illuminate\Http\Request;

class WorkspaceUrlCipher
{
    private const SALT = 'dcms_workspace_secure_routing_salt_v1';

    public const MODULE_SLUGS = [
        1 => 'dashboard',
        2 => 'report',
        3 => 'cek-ar',
        4 => 'format-keep',
        5 => 'tracking-ar',
        6 => 'klaim-ar',
        7 => 'arsip',
        8 => 'settings',
        9 => 'inbox-fu',
    ];

    private static function getKey(): string
    {
        $appKey = (string) config('app.key', 'base64:dcmsdefaultsecrethashfallback123');
        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            if ($decoded !== false) {
                $appKey = $decoded;
            }
        }
        return hash('sha256', $appKey . self::SALT, true);
    }

    private static function getIv(): string
    {
        return substr(hash('sha256', self::SALT), 0, 16);
    }

    /**
     * Encrypt a tab index (1..9) into a clean, deterministic URL-safe encrypted token.
     */
    public static function encode(int|string $tab): string
    {
        $tab = (int) $tab;
        $key = self::getKey();
        $iv = self::getIv();
        $encrypted = openssl_encrypt((string) $tab, 'AES-256-CBC', $key, 0, $iv);
        return rtrim(strtr((string) $encrypted, '+/', '-_'), '=');
    }

    /**
     * Decrypt an encrypted URL token into its corresponding tab index (1..9).
     */
    public static function decode(?string $token): ?int
    {
        if ($token === null || $token === '') {
            return null;
        }

        // 1. Try AES-256-CBC decryption
        $key = self::getKey();
        $iv = self::getIv();
        $base64 = strtr($token, '-_', '+/');
        $decrypted = openssl_decrypt($base64, 'AES-256-CBC', $key, 0, $iv);
        if ($decrypted !== false && is_numeric($decrypted)) {
            $tab = (int) $decrypted;
            if ($tab >= 1 && $tab <= 9) {
                return $tab;
            }
        }

        // 2. Fallback: Check if token is already a plain tab number
        if (is_numeric($token)) {
            $tab = (int) $token;
            if ($tab >= 1 && $tab <= 9) {
                return $tab;
            }
        }

        // 3. Fallback: Check if token matches a module slug
        $slug = strtolower(trim($token));
        foreach (self::MODULE_SLUGS as $tabIndex => $moduleSlug) {
            if ($slug === $moduleSlug) {
                return $tabIndex;
            }
        }

        return null;
    }

    /**
     * Resolve the active tab from an incoming HTTP request.
     */
    public static function resolveTab(Request $request): int
    {
        $token = $request->query('tab') 
            ?? $request->query('v') 
            ?? $request->query('token') 
            ?? $request->route('token');

        return self::decode($token) ?? 1;
    }

    /**
     * Get pre-computed dictionary of all tab encrypted tokens for client JS.
     *
     * @return array<int, string>
     */
    public static function allTokens(): array
    {
        $tokens = [];
        for ($i = 1; $i <= 9; $i++) {
            $tokens[$i] = self::encode($i);
        }
        return $tokens;
    }

    /**
     * Generate an encrypted URL for a given workspace tab index.
     */
    public static function url(int $tab): string
    {
        return RouteCipher::url('workspace-sosmed.index', ['tab' => $tab]);
    }
}

