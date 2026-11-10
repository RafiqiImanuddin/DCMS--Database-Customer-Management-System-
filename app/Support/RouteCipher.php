<?php

namespace App\Support;

use Illuminate\Http\Request;

class RouteCipher
{
    private const SALT = 'dcms_global_route_cipher_salt_2026';

    /**
     * Map of supported named routes and their target handlers
     */
    public const ROUTE_MAP = [
        'dashboard' => [
            'controller' => \App\Http\Controllers\DashboardController::class,
            'method' => '__invoke',
        ],
        'imports.index' => [
            'controller' => \App\Http\Controllers\CrossSellingImportController::class,
            'method' => 'index',
        ],
        'imports.destroy' => [
            'controller' => \App\Http\Controllers\CrossSellingImportController::class,
            'method' => 'destroy',
        ],
        'imports.destroy_turnover' => [
            'controller' => \App\Http\Controllers\CrossSellingImportController::class,
            'method' => 'destroyTurnover',
        ],
        'imports.template' => [
            'controller' => \App\Http\Controllers\CrossSellingImportController::class,
            'method' => 'template',
        ],
        'imports.template_turnover' => [
            'controller' => \App\Http\Controllers\CrossSellingImportController::class,
            'method' => 'templateTurnover',
        ],
        'predictions.rebuild' => [
            'controller' => \App\Http\Controllers\RepeatOrderPredictionController::class,
            'method' => 'rebuild',
        ],
        'predictions.index' => [
            'controller' => \App\Http\Controllers\RepeatOrderPredictionController::class,
            'method' => '__invoke',
        ],
        'predictions.download' => [
            'controller' => \App\Http\Controllers\RepeatOrderPredictionController::class,
            'method' => 'download',
        ],
        'leads.index' => [
            'controller' => \App\Http\Controllers\LeadCustomerController::class,
            'method' => 'index',
        ],
        'leads.download' => [
            'controller' => \App\Http\Controllers\LeadCustomerController::class,
            'method' => 'download',
        ],
        'broadcasts.index' => [
            'controller' => \App\Http\Controllers\BroadcastReportController::class,
            'method' => 'index',
        ],
        'broadcasts.download' => [
            'controller' => \App\Http\Controllers\BroadcastReportController::class,
            'method' => 'download',
        ],
        'broadcasts.templates.index' => [
            'controller' => \App\Http\Controllers\BroadcastTemplateController::class,
            'method' => 'index',
        ],
        'broadcasts.batches.show' => [
            'controller' => \App\Http\Controllers\BroadcastReportController::class,
            'method' => 'showBatch',
        ],
        'broadcasts.batches.download' => [
            'controller' => \App\Http\Controllers\BroadcastReportController::class,
            'method' => 'downloadBatch',
        ],
        'pricelist.index' => [
            'controller' => \App\Http\Controllers\PricelistController::class,
            'method' => 'index',
        ],
        'settings.watzap.index' => [
            'controller' => \App\Http\Controllers\WatzapSettingController::class,
            'method' => 'index',
        ],
        'settings.karyawan.index' => [
            'controller' => \App\Http\Controllers\EmployeeSettingController::class,
            'method' => 'index',
        ],
        'settings.users.index' => [
            'controller' => \App\Http\Controllers\UserController::class,
            'method' => 'manageUsers',
        ],
        'monitoring.sales.index' => [
            'controller' => \App\Http\Controllers\SalesMonitoringController::class,
            'method' => 'index',
        ],
        'quotations.index' => [
            'controller' => \App\Http\Controllers\QuotationController::class,
            'method' => 'index',
        ],
        'quotations.create' => [
            'controller' => \App\Http\Controllers\QuotationController::class,
            'method' => 'create',
        ],
        'quotations.show' => [
            'controller' => \App\Http\Controllers\QuotationController::class,
            'method' => 'show',
        ],
        'quotations.edit' => [
            'controller' => \App\Http\Controllers\QuotationController::class,
            'method' => 'edit',
        ],
        'quotations.print' => [
            'controller' => \App\Http\Controllers\QuotationController::class,
            'method' => 'print',
        ],
        'guidance.index' => [
            'controller' => \App\Http\Controllers\GuidanceController::class,
            'method' => 'index',
        ],
        'whatsapp.index' => [
            'controller' => \App\Http\Controllers\WatzapSettingController::class,
            'method' => 'index',
        ],
        'workspace-sosmed.index' => [
            'controller' => \App\Http\Controllers\WorkspaceSosmedController::class,
            'method' => 'index',
        ],
        'profile.edit' => [
            'controller' => \App\Http\Controllers\UserController::class,
            'method' => 'profile',
        ],
        'register' => [
            'controller' => \App\Http\Controllers\UserController::class,
            'method' => 'create',
        ],
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
     * Encrypt any route and its parameters into a deterministic, URL-safe token.
     */
    public static function encode(string $route, array $params = []): string
    {
        ksort($params);
        $payload = json_encode(['r' => $route, 'p' => $params]);
        $key = self::getKey();
        $iv = self::getIv();
        $encrypted = openssl_encrypt((string) $payload, 'AES-256-CBC', $key, 0, $iv);
        return rtrim(strtr((string) $encrypted, '+/', '-_'), '=');
    }

    /**
     * Decrypt an encrypted route token into ['route' => ..., 'params' => ...].
     */
    public static function decode(?string $token): ?array
    {
        if ($token === null || $token === '') {
            return null;
        }

        // 1. Try AES-256-CBC decryption
        $key = self::getKey();
        $iv = self::getIv();
        $base64 = strtr($token, '-_', '+/');
        $decrypted = openssl_decrypt($base64, 'AES-256-CBC', $key, 0, $iv);
        if ($decrypted !== false) {
            $data = json_decode($decrypted, true);
            if (is_array($data) && isset($data['r'])) {
                return [
                    'route' => $data['r'],
                    'params' => $data['p'] ?? [],
                ];
            }
        }

        // 2. Fallback: If route alias is passed directly
        if (isset(self::ROUTE_MAP[$token])) {
            return [
                'route' => $token,
                'params' => [],
            ];
        }

        // 3. Compatibility with WorkspaceUrlCipher token
        $tab = WorkspaceUrlCipher::decode($token);
        if ($tab !== null) {
            return [
                'route' => 'workspace-sosmed.index',
                'params' => ['tab' => $tab],
            ];
        }

        return null;
    }

    /**
     * Generate an encrypted URL for any named route.
     */
    public static function url(string $route, array $params = []): string
    {
        $token = self::encode($route, $params);
        return route('encrypted.route', ['token' => $token]);
    }

    /**
     * Generate all encrypted URLs for workspace tabs (1..9).
     *
     * @return array<int, string>
     */
    public static function allWorkspaceUrls(): array
    {
        $urls = [];
        for ($i = 1; $i <= 9; $i++) {
            $urls[$i] = self::url('workspace-sosmed.index', ['tab' => $i]);
        }
        return $urls;
    }

    /**
     * Resolve the underlying route identifier for the current request.
     */
    public static function resolveTarget(Request $request): string
    {
        if ($request->routeIs('encrypted.route')) {
            $token = (string) $request->route('token');
            $decoded = self::decode($token);
            if ($decoded) {
                return $decoded['route'];
            }
        }

        if ($request->routeIs('dashboard')) return 'dashboard';
        if ($request->routeIs('imports.*')) return 'imports.index';
        if ($request->routeIs('predictions.*')) return 'predictions.index';
        if ($request->routeIs('leads.*')) return 'leads.index';
        if ($request->routeIs('broadcasts.*')) return 'broadcasts.index';
        if ($request->routeIs('monitoring.sales.*')) return 'monitoring.sales.index';
        if ($request->routeIs('guidance.*')) return 'guidance.index';
        if ($request->routeIs('settings.karyawan.*')) return 'settings.karyawan.index';
        if ($request->routeIs('settings.watzap.*', 'whatsapp.*')) return 'settings.watzap.index';
        if ($request->routeIs('workspace-sosmed.*')) return 'workspace-sosmed.index';

        return '';
    }

    /**
     * Resolve active workspace tab index (1..9) if currently on a workspace route.
     */
    public static function resolveWorkspaceTab(Request $request): ?int
    {
        if ($request->routeIs('encrypted.route')) {
            $token = (string) $request->route('token');
            $decoded = self::decode($token);
            if ($decoded && $decoded['route'] === 'workspace-sosmed.index') {
                return (int) ($decoded['params']['tab'] ?? 1);
            }
        }

        if ($request->routeIs('workspace-sosmed.*')) {
            return WorkspaceUrlCipher::resolveTab($request);
        }

        return null;
    }

    /**
     * Get the encrypted URL that corresponds to the current request if not already encrypted.
     */
    public static function resolveCurrentEncryptedUrl(Request $request): ?string
    {
        if ($request->routeIs('encrypted.route')) {
            return null;
        }

        $target = self::resolveTarget($request);
        if ($target === '') {
            return null;
        }

        if ($target === 'workspace-sosmed.index') {
            $tab = WorkspaceUrlCipher::resolveTab($request);
            return self::url('workspace-sosmed.index', ['tab' => $tab]);
        }

        $params = $request->query();
        unset($params['_token'], $params['_method']);

        return self::url($target, $params);
    }
}
