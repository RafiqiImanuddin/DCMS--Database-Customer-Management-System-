<?php

namespace App\Http\Controllers;

use App\Support\RouteCipher;
use Illuminate\Http\Request;

class EncryptedRouteController extends Controller
{
    /**
     * Dispatch an incoming encrypted route token to its corresponding controller action.
     */
    public function __invoke(Request $request, string $token)
    {
        // Bersihkan token dari query string ganda atau karakter pemisah URL yang tidak disengaja
        if (str_contains($token, '?')) {
            $parts = explode('?', $token, 2);
            $token = $parts[0];
            parse_str($parts[1] ?? '', $extra);
            if (! empty($extra)) {
                $request->query->add($extra);
            }
        }

        $decoded = RouteCipher::decode($token);
        if (! $decoded || ! isset(RouteCipher::ROUTE_MAP[$decoded['route']])) {
            // Fallback toleran jika ada parameter tab yang dikirimkan
            if ($request->has('tab')) {
                $tab = (int) $request->query('tab');
                if ($tab >= 1 && $tab <= 12) {
                    return redirect()->to(RouteCipher::url('workspace-sosmed.index', ['tab' => $tab]));
                }
            }

            abort(404, 'Tautan routing terenkripsi tidak valid.');
        }

        $config = RouteCipher::ROUTE_MAP[$decoded['route']];
        $controllerClass = $config['controller'];
        $method = $config['method'];

        $routeParams = $decoded['params'] ?? [];
        if (! empty($routeParams)) {
            $request->query->add($routeParams);
            if ($request->route()) {
                foreach ($routeParams as $key => $val) {
                    $request->route()->setParameter($key, $val);
                }
            }
        }

        $controller = app()->make($controllerClass);

        return app()->call([$controller, $method], array_merge(['request' => $request], $routeParams));
    }
}
