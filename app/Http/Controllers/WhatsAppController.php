<?php

namespace App\Http\Controllers;

use App\Services\WatzapService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function index(Request $request, WatzapService $watzap): View
    {
        $deviceStatus = null;
        if ($watzap->isConfigured()) {
            $deviceStatus = $watzap->getDeviceStatus();
        }

        return view('whatsapp.index', [
            'isConfigured' => $watzap->isConfigured(),
            'webUrl' => $watzap->getWebUrl(),
            'deviceStatus' => $deviceStatus,
            'isSuperAdmin' => $request->user()->isSuperAdmin(),
        ]);
    }
}
