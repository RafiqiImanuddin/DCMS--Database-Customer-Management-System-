<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        'shifts.index' => [
            'controller' => \App\Http\Controllers\SosmedShiftController::class,
            'method' => 'index',
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

    /**
     * Pre-defined Masked Route Tokens in strict xxxx-xxxx-xxxx format (Two-Way Hash Registry)
     */
    public const MASKED_ROUTE_MAP = [
        'd0ba-9a7f-8947' => ['route' => 'dashboard', 'params' => []],
        '6b59-fc91-59c1' => ['route' => 'predictions.index', 'params' => []],
        '8c9d-92fa-f5a7' => ['route' => 'leads.index', 'params' => []],
        '05d7-989f-827f' => ['route' => 'broadcasts.index', 'params' => []],
        '5977-c6b1-ad30' => ['route' => 'broadcasts.templates.index', 'params' => []],
        '919e-607a-a50a' => ['route' => 'pricelist.index', 'params' => []],
        'cede-c202-ed0b' => ['route' => 'monitoring.sales.index', 'params' => []],
        '4888-95d4-2573' => ['route' => 'shifts.index', 'params' => []],
        '63f3-c80f-0124' => ['route' => 'quotations.index', 'params' => []],
        '32d9-b236-5d5d' => ['route' => 'quotations.create', 'params' => []],
        'd2b6-d192-fed7' => ['route' => 'file-manager.index', 'params' => []],
        'f75c-eb16-c76b' => ['route' => 'guidance.index', 'params' => []],
        '72a4-239c-bc58' => ['route' => 'imports.index', 'params' => []],
        '5938-494d-edc4' => ['route' => 'settings.watzap.index', 'params' => []],
        '431f-17a4-4ae5' => ['route' => 'settings.karyawan.index', 'params' => []],
        '713e-bc6c-5ff4' => ['route' => 'settings.karyawan.index', 'params' => ['tab' => 'monthly_targets']],
        '23dc-bdeb-6b25' => ['route' => 'settings.users.index', 'params' => []],
        '0c2e-c991-7914' => ['route' => 'profile.edit', 'params' => []],

        // Workspace Sosmed Tabs 1..12
        'd834-6dc8-183e' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 1]],
        '65b1-c3e3-60e6' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 2]],
        '7ca6-0b37-2986' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 3]],
        'c4cf-6d71-6599' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 4]],
        'b9e1-430a-3bfe' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 5]],
        '2ea9-2686-60f1' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 6]],
        '4f19-b462-4759' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 7]],
        'fa18-e147-d14c' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 8]],
        '9fe8-8336-368e' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 9]],
        '94de-1397-8c7e' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 10]],
        '4438-70e4-0461' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 11]],
        '96f0-d811-d2dd' => ['route' => 'workspace-sosmed.index', 'params' => ['tab' => 12]],
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
     * Get predefined xxxx-xxxx-xxxx token for a route and its parameters.
     */
    public static function getStaticToken(string $route, array $params = []): ?string
    {
        // Normalize params: sort and remove empty/null
        $cleanParams = array_filter($params, fn ($v) => $v !== null && $v !== '');
        ksort($cleanParams);

        // Normalize tab param to int if workspace-sosmed
        if ($route === 'workspace-sosmed.index' && isset($cleanParams['tab'])) {
            $cleanParams['tab'] = (int) $cleanParams['tab'];
        }

        foreach (self::MASKED_ROUTE_MAP as $token => $def) {
            if ($def['route'] !== $route) {
                continue;
            }

            $defParams = $def['params'] ?? [];
            ksort($defParams);

            if ($defParams == $cleanParams) {
                return $token;
            }
        }

        return null;
    }

    /**
     * Encrypt any route and its parameters into a deterministic, URL-safe token in xxxx-xxxx-xxxx format.
     */
    public static function encode(string $route, array $params = []): string
    {
        // 1. Check predefined static registry
        $staticToken = self::getStaticToken($route, $params);
        if ($staticToken !== null) {
            return $staticToken;
        }

        // 2. Generate dynamic deterministic xxxx-xxxx-xxxx token
        ksort($params);
        $payload = json_encode(['r' => $route, 'p' => $params]);
        $hash = substr(hash('sha256', self::SALT . '_' . $payload), 0, 12);
        $token = substr($hash, 0, 4) . '-' . substr($hash, 4, 4) . '-' . substr($hash, 8, 4);

        // Cache dynamic token mapping for 6 months
        try {
            Cache::put('route_mask_' . $token, ['route' => $route, 'params' => $params], now()->addMonths(6));
        } catch (\Throwable) {}

        return $token;
    }

    /**
     * Decrypt an encrypted route token into ['route' => ..., 'params' => ...].
     */
    public static function decode(?string $token): ?array
    {
        if ($token === null || $token === '') {
            return null;
        }

        $token = trim((string) $token);
        if (str_contains($token, '?')) {
            $token = explode('?', $token, 2)[0];
        }

        // 1. Direct match in static registry
        if (isset(self::MASKED_ROUTE_MAP[$token])) {
            return self::MASKED_ROUTE_MAP[$token];
        }

        // 1b. Match case-insensitively or with/without dash
        $norm = strtolower($token);
        $normNoDash = str_replace('-', '', $norm);
        foreach (self::MASKED_ROUTE_MAP as $key => $val) {
            if ($norm === strtolower($key) || $normNoDash === str_replace('-', '', strtolower($key))) {
                return $val;
            }
        }

        // 2. Dynamic token from Cache
        try {
            $cached = Cache::get('route_mask_' . $token);
            if (is_array($cached) && isset($cached['route'])) {
                return $cached;
            }
        } catch (\Throwable) {}

        // 3. Compatibility with legacy AES-256-CBC token
        $legacy = self::decodeLegacy($token);
        if ($legacy !== null) {
            return $legacy;
        }

        // 4. Compatibility with direct route alias or slug
        if (isset(self::ROUTE_MAP[$token])) {
            return [
                'route' => $token,
                'params' => [],
            ];
        }

        // 5. Compatibility with WorkspaceUrlCipher token
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
     * Decode legacy AES base64 tokens.
     */
    protected static function decodeLegacy(string $token): ?array
    {
        try {
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
        } catch (\Throwable) {}

        return null;
    }

    /**
     * Generate an encrypted URL for any named route in xxxx-xxxx-xxxx format.
     */
    public static function url(string $route, array $params = []): string
    {
        $token = self::encode($route, $params);
        return route('encrypted.route', ['token' => $token]);
    }

    /**
     * Generate all encrypted URLs for workspace tabs (1..12) in xxxx-xxxx-xxxx format.
     *
     * @return array<int, string>
     */
    public static function allWorkspaceUrls(): array
    {
        $urls = [];
        for ($i = 1; $i <= 12; $i++) {
            $urls[$i] = self::url('workspace-sosmed.index', ['tab' => $i]);
        }
        return $urls;
    }

    /**
     * Pre-computed dictionary of all workspace tokens (1..12).
     *
     * @return array<int, string>
     */
    public static function allWorkspaceTokens(): array
    {
        return WorkspaceUrlCipher::allTokens();
    }

    /**
     * Resolve the underlying route identifier for the current request.
     */
    public static function resolveTarget(Request $request): string
    {
        if ($request->routeIs('encrypted.route') || preg_match('/^[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}$/', (string) $request->route('token'))) {
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
        if ($request->routeIs('broadcasts.templates.*')) return 'broadcasts.templates.index';
        if ($request->routeIs('broadcasts.*')) return 'broadcasts.index';
        if ($request->routeIs('pricelist.*')) return 'pricelist.index';
        if ($request->routeIs('monitoring.sales.*')) return 'monitoring.sales.index';
        if ($request->routeIs('shifts.*')) return 'shifts.index';
        if ($request->routeIs('quotations.create')) return 'quotations.create';
        if ($request->routeIs('quotations.*')) return 'quotations.index';
        if ($request->routeIs('file-manager.*')) return 'file-manager.index';
        if ($request->routeIs('guidance.*')) return 'guidance.index';
        if ($request->routeIs('settings.karyawan.*')) return 'settings.karyawan.index';
        if ($request->routeIs('settings.users.*')) return 'settings.users.index';
        if ($request->routeIs('settings.watzap.*', 'whatsapp.*')) return 'settings.watzap.index';
        if ($request->routeIs('profile.*')) return 'profile.edit';
        if ($request->routeIs('workspace-sosmed.*')) return 'workspace-sosmed.index';

        return '';
    }

    /**
     * Resolve active workspace tab index (1..12) if currently on a workspace route.
     */
    public static function resolveWorkspaceTab(Request $request): ?int
    {
        if ($request->routeIs('encrypted.route') || preg_match('/^[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}$/', (string) $request->route('token'))) {
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
     * Get the masked URL that corresponds to the current request if not already encrypted.
     */
    public static function resolveCurrentEncryptedUrl(Request $request): ?string
    {
        if ($request->routeIs('encrypted.route')) {
            $token = (string) $request->route('token');
            // If already masked in xxxx-xxxx-xxxx format and no raw query string, no need to redirect
            if (preg_match('/^[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}$/', $token) && empty($request->query())) {
                return null;
            }
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
