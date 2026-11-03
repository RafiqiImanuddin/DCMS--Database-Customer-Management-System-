<?php

namespace App\Http\Controllers;

use App\Models\BroadcastMedia;
use App\Models\BroadcastTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastTemplateController extends Controller
{
    public function index(): View
    {
        $templates = BroadcastTemplate::orderBy('id')->get();

        return view('broadcasts.templates', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2500'],
            'image' => ['nullable', 'file', 'image', 'max:5120'], // max 5MB
            'image_url' => ['nullable', 'url', 'max:500'],
        ]);

        $imageUrl = $validated['image_url'] ?? null;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $media = BroadcastMedia::create([
                'user_id' => $request->user()->id,
                'filename' => $file->getClientOriginalName() ?: 'broadcast-image.jpg',
                'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                'file_size' => $file->getSize(),
                'data' => base64_encode($file->getContent()),
            ]);
            $imageUrl = route('broadcasts.media.show', ['media' => $media->id], true);
        }

        BroadcastTemplate::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'image_url' => $imageUrl,
        ]);

        return back()->with('success', 'Template broadcast "'.$validated['title'].'" berhasil disimpan.');
    }

    public function update(Request $request, BroadcastTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2500'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:500'],
        ]);

        $imageUrl = $validated['image_url'] ?? $template->image_url;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $media = BroadcastMedia::create([
                'user_id' => $request->user()->id,
                'filename' => $file->getClientOriginalName() ?: 'broadcast-image.jpg',
                'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                'file_size' => $file->getSize(),
                'data' => base64_encode($file->getContent()),
            ]);
            $imageUrl = route('broadcasts.media.show', ['media' => $media->id], true);
        }

        $template->update([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'image_url' => $imageUrl,
        ]);

        return back()->with('success', 'Template broadcast berhasil diperbarui.');
    }

    public function destroy(BroadcastTemplate $template): RedirectResponse
    {
        $title = $template->title;
        $template->delete();

        return back()->with('success', 'Template "'.$title.'" berhasil dihapus.');
    }
}

