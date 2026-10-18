<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UI strings for the React frontend, one payload per locale.
 */
final class TranslationController extends Controller
{
    public function show(Request $request, string $locale): JsonResponse
    {
        $version = Translations::version($locale);

        $response = response()->json(
            Translations::messages($locale),
            options: JSON_UNESCAPED_UNICODE,
        );

        // A request for the current version can be cached for good: any change
        // to the strings produces a new version and therefore a new URL.
        if ($request->query('v') === $version) {
            return $response->setPublic()->setMaxAge(31_536_000)->setImmutable();
        }

        return $response->setPublic()->setMaxAge(0)->setEtag($version);
    }
}
