<?php

namespace Tests\Unit;

use App\Support\WorkspaceUrlCipher;
use Illuminate\Http\Request;
use Tests\TestCase;

class WorkspaceUrlCipherTest extends TestCase
{
    public function test_can_encode_and_decode_all_workspace_tabs(): void
    {
        for ($i = 1; $i <= 9; $i++) {
            $token = WorkspaceUrlCipher::encode($i);
            $this->assertNotEmpty($token);
            $this->assertStringNotContainsString('+', $token);
            $this->assertStringNotContainsString('/', $token);
            $this->assertStringNotContainsString('=', $token);

            $decoded = WorkspaceUrlCipher::decode($token);
            $this->assertSame($i, $decoded);
        }
    }

    public function test_decode_falls_back_to_raw_numeric_tab(): void
    {
        $this->assertSame(9, WorkspaceUrlCipher::decode('9'));
        $this->assertSame(1, WorkspaceUrlCipher::decode('1'));
        $this->assertSame(5, WorkspaceUrlCipher::decode('5'));
        $this->assertNull(WorkspaceUrlCipher::decode('99'));
    }

    public function test_decode_supports_slugs(): void
    {
        $this->assertSame(9, WorkspaceUrlCipher::decode('inbox-fu'));
        $this->assertSame(1, WorkspaceUrlCipher::decode('dashboard'));
        $this->assertSame(2, WorkspaceUrlCipher::decode('report'));
        $this->assertSame(3, WorkspaceUrlCipher::decode('cek-ar'));
    }

    public function test_resolve_tab_from_request(): void
    {
        // 1. From encrypted token in 'tab'
        $token = WorkspaceUrlCipher::encode(9);
        $request = Request::create('/workspace-sosmed', 'GET', ['tab' => $token]);
        $this->assertSame(9, WorkspaceUrlCipher::resolveTab($request));

        // 2. From 'v' query param
        $token = WorkspaceUrlCipher::encode(4);
        $request = Request::create('/workspace-sosmed', 'GET', ['v' => $token]);
        $this->assertSame(4, WorkspaceUrlCipher::resolveTab($request));

        // 3. Default to 1 if no param
        $request = Request::create('/workspace-sosmed', 'GET');
        $this->assertSame(1, WorkspaceUrlCipher::resolveTab($request));
    }
}

