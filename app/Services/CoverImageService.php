<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CoverImageService
{
    private const PATH_RULE = '#^covers/[A-Za-z0-9]+\.(jpg|jpeg|png|webp)$#';

    /** Apply the cover-image part of an article form: a new upload, a removal, or just new alt text. */
    public function apply(Request $request, Article $article): void
    {
        $alt = trim((string) $request->input('cover_alt', ''));
        $alt = $alt !== '' ? $alt : null;
        $old = $article->cover_image;

        if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
            $file = $request->file('cover');
            $name = $file->hashName();   // random name; the extension comes from the real file type
            $file->move(public_path('uploads/covers'), $name);

            $article->forceFill(['cover_image' => 'covers/' . $name, 'cover_alt' => $alt])->save();
            $this->delete($old);

            return;
        }

        if ($request->boolean('remove_cover') && $old) {
            $article->forceFill(['cover_image' => null, 'cover_alt' => null])->save();
            $this->delete($old);

            return;
        }

        if ($old && $request->has('cover_alt')) {
            $article->forceFill(['cover_alt' => $alt])->save();
        }
    }

    /** Remove a stored cover file. Only paths this service created are ever deleted. */
    public function delete(?string $path): void
    {
        if ($path && preg_match(self::PATH_RULE, $path)) {
            File::delete(public_path('uploads/' . $path));
        }
    }
}