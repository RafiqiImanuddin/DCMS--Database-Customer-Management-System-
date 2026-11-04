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
        $decoded = RouteCipher::decode($token);
        if (! $decoded || ! isset(RouteCipher::ROUTE_MAP[$decoded['route']])) {
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
