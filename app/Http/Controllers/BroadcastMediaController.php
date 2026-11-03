<?php

namespace App\Http\Controllers;

use App\Models\BroadcastMedia;
use Symfony\Component\HttpFoundation\Response;

class BroadcastMediaController extends Controller
{
    /**
     * Publicly serve uploaded broadcast media so external services (like WatZap)
     * can fetch the image without authentication headers.
     */
    public function show(BroadcastMedia $media): Response
    {
        $binary = base64_decode($media->data);

        return response($binary, 200, [
            'Content-Type' => $media->mime_type ?: 'image/jpeg',
            'Content-Length' => strlen($binary),
            'Cache-Control' => 'public, max-age=86400',
            'Content-Disposition' => 'inline; filename="'.addslashes($media->filename).'"',
        ]);
    }
}

