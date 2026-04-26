<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class WindowsSafeFilesystem extends Filesystem
{
    public function replace($path, $content, $mode = null): void
    {
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;
        $directory = dirname($path);
        $tempPath = tempnam($directory, basename($path));

        if ($tempPath === false) {
            throw new RuntimeException("Unable to create a temporary file for [{$path}].");
        }

        if (! is_null($mode)) {
            @chmod($tempPath, $mode);
        } else {
            @chmod($tempPath, 0777 - umask());
        }

        file_put_contents($tempPath, $content);

        if (DIRECTORY_SEPARATOR !== '\\') {
            rename($tempPath, $path);

            return;
        }

        $attempts = 0;

        while ($attempts < 5) {
            if (@rename($tempPath, $path)) {
                return;
            }

            clearstatcache(true, $path);
            usleep(100000);
            $attempts++;
        }

        if (@copy($tempPath, $path)) {
            @unlink($tempPath);

            return;
        }

        @unlink($tempPath);

        throw new RuntimeException("Unable to replace file [{$path}] after multiple attempts.");
    }
}
