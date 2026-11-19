<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Keeps the file handling for vehicle pictures in one place so the
 * admin panel and the REST API behave exactly the same way.
 */
class ShowroomImage
{
    private const DISK = 'public';

    private const FOLDER = 'models';

    /** Save an uploaded picture and return its path relative to the disk. */
    public function store(UploadedFile $file): string
    {
        return $file->store(self::FOLDER, self::DISK);
    }

    /** Remove an old picture. Safe to call with null / missing files. */
    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /** Replace $old with the new upload and return the new path. */
    public function replace(?string $old, UploadedFile $file): string
    {
        $new = $this->store($file);
        $this->delete($old);

        return $new;
    }
}
