<?php

namespace App\Http\Controllers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function show(Media $media, ?string $conversion = null)
    {
        if ($conversion) {
            if (!$media->hasGeneratedConversion($conversion)) {
                abort(404);
            }
            $path = $media->getPath($conversion);
        } else {
            $path = $media->getPath();
        }

        if (!$path || !is_file($path)) {
            abort(404);
        }

        $mime = $media->mime_type ?: mime_content_type($path) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
