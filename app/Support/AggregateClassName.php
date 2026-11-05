<?php

namespace App\Support;

use SplFileInfo;

/**
 * Maps a PHP file to its class name for Laravel's event discovery. Files in
 * `src/{Aggregate}/` belong to the `{Aggregate}\` PSR-4 root (composer.json),
 * files in `app/` to `App\`. Laravel's default guess would produce
 * `Src\Tasks\...` for aggregates.
 */
final class AggregateClassName
{
    /**
     * @return class-string
     */
    public static function fromFile(SplFileInfo $file, string $basePath): string
    {
        $relative = ltrim(str_replace('\\', '/', substr((string) $file->getRealPath(), strlen(rtrim($basePath, '/\\')))), '/');
        $relative = preg_replace('/\.php$/', '', $relative) ?? $relative;

        $class = match (true) {
            str_starts_with($relative, 'src/') => substr($relative, strlen('src/')),
            str_starts_with($relative, 'app/') => 'App/'.substr($relative, strlen('app/')),
            default => ucfirst($relative),
        };

        /** @var class-string */
        return str_replace('/', '\\', $class);
    }
}
