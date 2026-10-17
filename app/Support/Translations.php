<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Serves the application's lang/ strings to the frontend.
 *
 * The payload mirrors Laravel's own lookup: PHP group files flattened to
 * "group.key.sub" plus the JSON string table, with the configured fallback
 * locale filled in underneath the requested one. The version is derived from
 * the lang files themselves, so a deploy that changes a string changes the URL
 * and browsers fetch it again; otherwise it is cached indefinitely.
 */
final class Translations
{
    /** @var list<string> */
    public const LOCALES = ['en', 'am'];

    public static function version(string $locale): string
    {
        $stamp = collect([self::directory($locale), self::directory(self::fallback())])
            ->unique()
            ->flatMap(fn (string $dir): array => File::isDirectory($dir) ? File::allFiles($dir) : [])
            ->map(fn (\SplFileInfo $file): string => $file->getPathname().':'.$file->getMTime())
            ->merge(collect([$locale, self::fallback()])->map(fn (string $l): string => self::jsonPath($l).':'.(is_file(self::jsonPath($l)) ? filemtime(self::jsonPath($l)) : 0)))
            ->sort()
            ->implode('|');

        return substr(hash('sha256', $locale.'|'.self::fallback().'|'.$stamp), 0, 16);
    }

    /**
     * @return array<string, string>
     */
    public static function messages(string $locale): array
    {
        return Cache::rememberForever(
            'i18n:messages:'.$locale.':'.self::version($locale),
            fn (): array => array_merge(self::load(self::fallback()), self::load($locale)),
        );
    }

    /**
     * @return array<string, string>
     */
    private static function load(string $locale): array
    {
        $messages = [];

        foreach (glob(self::directory($locale).'/*.php') ?: [] as $file) {
            $group = basename($file, '.php');
            $lines = require $file;
            if (is_array($lines)) {
                foreach (Arr::dot($lines) as $key => $value) {
                    if (is_string($value)) {
                        $messages[$group.'.'.$key] = $value;
                    }
                }
            }
        }

        $json = self::jsonPath($locale);
        if (is_file($json)) {
            $decoded = json_decode((string) file_get_contents($json), true);
            foreach (is_array($decoded) ? $decoded : [] as $key => $value) {
                if (is_string($key) && is_string($value)) {
                    $messages[$key] = $value;
                }
            }
        }

        return $messages;
    }

    private static function fallback(): string
    {
        return (string) config('app.fallback_locale', 'en');
    }

    private static function directory(string $locale): string
    {
        return lang_path($locale);
    }

    private static function jsonPath(string $locale): string
    {
        return lang_path($locale.'.json');
    }
}
