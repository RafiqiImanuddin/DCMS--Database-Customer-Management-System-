<?php

namespace Tests\Unit;

use App\Support\RouteCipher;
use App\Support\WorkspaceUrlCipher;
use Illuminate\Http\Request;
use Tests\TestCase;

class RouteCipherTest extends TestCase
{
    public function test_can_encode_and_decode_all_major_routes(): void
    {
        $routes = [
            'dashboard' => [],
            'imports.index' => [],
            'predictions.index' => ['page' => 2],
            'leads.index' => ['search' => 'budi'],
            'broadcasts.index' => ['tab' => 'all'],
            'settings.watzap.index' => [],
            'workspace-sosmed.index' => ['tab' => 9],
        ];

        foreach ($routes as $route => $params) {
            $token = RouteCipher::encode($route, $params);
            $this->assertNotEmpty($token);
            $this->assertStringNotContainsString('+', $token);
            $this->assertStringNotContainsString('/', $token);
            $this->assertStringNotContainsString('=', $token);

            $decoded = RouteCipher::decode($token);
            $this->assertNotNull($decoded);
            $this->assertSame($route, $decoded['route']);
            $this->assertSame($params, $decoded['params']);
        }
    }

    public function test_url_generates_encrypted_gateway_link(): void
    {
        $url = RouteCipher::url('dashboard');
        $this->assertStringContainsString('/r/', $url);

        $wsUrl = RouteCipher::url('workspace-sosmed.index', ['tab' => 9]);
        $this->assertStringContainsString('/r/', $wsUrl);
    }

    public function test_decode_supports_workspace_url_cipher_tokens(): void
    {
        $wsToken = WorkspaceUrlCipher::encode(9);
        $decoded = RouteCipher::decode($wsToken);

        $this->assertNotNull($decoded);
        $this->assertSame('workspace-sosmed.index', $decoded['route']);
        $this->assertSame(['tab' => 9], $decoded['params']);
    }

    public function test_decode_supports_route_alias_fallback(): void
    {
        $decoded = RouteCipher::decode('dashboard');
        $this->assertNotNull($decoded);
        $this->assertSame('dashboard', $decoded['route']);
    }

    public function test_resolve_target_from_request(): void
    {
        $token = RouteCipher::encode('leads.index');
        $request = Request::create('/r/' . $token, 'GET');
        $route = new \Illuminate\Routing\Route('GET', '/r/{token}', []);
        $route->name('encrypted.route');
        $route->bind($request);
        $route->setParameter('token', $token);
        $request->setRouteResolver(fn () => $route);

        $this->assertSame('leads.index', RouteCipher::resolveTarget($request));
    }

    public function test_resolve_workspace_tab_from_request(): void
    {
        $token = RouteCipher::encode('workspace-sosmed.index', ['tab' => 6]);
        $request = Request::create('/r/' . $token, 'GET');
        $route = new \Illuminate\Routing\Route('GET', '/r/{token}', []);
        $route->name('encrypted.route');
        $route->bind($request);
        $route->setParameter('token', $token);
        $request->setRouteResolver(fn () => $route);

        $this->assertSame(6, RouteCipher::resolveWorkspaceTab($request));
    }
}
