<?php

namespace App\Support;

use Illuminate\Http\Request;

class WorkspaceUrlCipher
{
    private const SALT = 'dcms_workspace_secure_routing_salt_v1';

    /**
     * Map of workspace tabs (1..12) to strict xxxx-xxxx-xxxx tokens
     */
    public const TAB_TOKENS = [
        1 => 'd834-6dc8-183e',
        2 => '65b1-c3e3-60e6',
        3 => '7ca6-0b37-2986',
        4 => 'c4cf-6d71-6599',
        5 => 'b9e1-430a-3bfe',
        6 => '2ea9-2686-60f1',
        7 => '4f19-b462-4759',
        8 => 'fa18-e147-d14c',
        9 => '9fe8-8336-368e',
        10 => '94de-1397-8c7e',
        11 => '4438-70e4-0461',
        12 => '96f0-d811-d2dd',
    ];

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
        10 => 'ola-cabang',
        11 => 'customer-segmentation',
        12 => 'auto-fu-h1',
    ];

    /**
     * Encode a tab index (1..12) into a masked xxxx-xxxx-xxxx token.
     */
    public static function encode(int|string $tab): string
    {
        $tab = (int) $tab;
        return self::TAB_TOKENS[$tab] ?? 'd834-6dc8-183e';
    }

    /**
     * Decrypt an encrypted URL token into its corresponding tab index (1..12).
     */
    public static function decode(?string $token): ?int
    {
        if ($token === null || $token === '') {
            return null;
        }

        $token = trim((string) $token);

        // 1. Check in TAB_TOKENS (xxxx-xxxx-xxxx)
        $norm = strtolower($token);
        $normNoDash = str_replace('-', '', $norm);
        foreach (self::TAB_TOKENS as $tab => $tok) {
            if ($norm === strtolower($tok) || $normNoDash === str_replace('-', '', strtolower($tok))) {
                return $tab;
            }
        }

        // 2. Check if token matches a module slug
        foreach (self::MODULE_SLUGS as $tabIndex => $moduleSlug) {
            if ($norm === $moduleSlug) {
                return $tabIndex;
            }
        }

        // 3. Fallback: Check if token is already a plain tab number
        if (is_numeric($token)) {
            $tab = (int) $token;
            if ($tab >= 1 && $tab <= 12) {
                return $tab;
            }
        }

        // 4. Fallback: Try legacy AES-256-CBC decryption
        try {
            $appKey = (string) config('app.key', 'base64:dcmsdefaultsecrethashfallback123');
            if (str_starts_with($appKey, 'base64:')) {
                $decodedKey = base64_decode(substr($appKey, 7), true);
                if ($decodedKey !== false) $appKey = $decodedKey;
            }
            $key = hash('sha256', $appKey . self::SALT, true);
            $iv = substr(hash('sha256', self::SALT), 0, 16);
            $base64 = strtr($token, '-_', '+/');
            $decrypted = openssl_decrypt($base64, 'AES-256-CBC', $key, 0, $iv);
            if ($decrypted !== false && is_numeric($decrypted)) {
                $tab = (int) $decrypted;
                if ($tab >= 1 && $tab <= 12) {
                    return $tab;
                }
            }
        } catch (\Throwable) {}

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
     * Get pre-computed dictionary of all tab tokens (1..12) in xxxx-xxxx-xxxx format.
     *
     * @return array<int, string>
     */
    public static function allTokens(): array
    {
        return self::TAB_TOKENS;
    }

    /**
     * Generate an encrypted URL for a given workspace tab index in xxxx-xxxx-xxxx format.
     */
    public static function url(int $tab): string
    {
        return RouteCipher::url('workspace-sosmed.index', ['tab' => $tab]);
    }
}
