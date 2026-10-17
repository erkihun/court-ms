<?php

declare(strict_types=1);

namespace App\Support;

use Tighten\Ziggy\Ziggy;

/**
 * The named routes the frontend's route() helper can build, embedded once in
 * the root view.
 *
 * Ziggy serialises every `where` constraint a route carries. The web routes
 * inherit one numeric constraint per ID-style parameter name from their group,
 * most of which a given route does not use; keeping only the constraints for
 * the route's own parameters cuts the payload from ~300 KB to ~45 KB.
 */
final class FrontendRoutes
{
    /**
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        $config = (new Ziggy)->toArray();

        foreach ($config['routes'] as &$route) {
            if (! isset($route['wheres'])) {
                continue;
            }

            $route['wheres'] = array_intersect_key($route['wheres'], array_flip($route['parameters'] ?? []));
            if ($route['wheres'] === []) {
                unset($route['wheres']);
            }
        }
        unset($route);

        return $config;
    }
}
