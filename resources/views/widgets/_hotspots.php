<?php

declare(strict_types=1);

/**
 * The fallback while the project's own `widgets/_hotspots` does not exist; a project styles `.hotspot` or replaces it.
 *
 * @see \Hirtz\Cms\Hotspot\Widgets\Artwork::renderHotspots()
 * @var list<Hotspot> $hotspots
 */

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Skeleton\Html\A;
use Hirtz\Skeleton\Html\Span;

foreach ($hotspots as $hotspot) {
    $link = $hotspot->getVisibleAttribute('link');

    echo ($link ? A::make()->href($link) : Span::make())
        ->attribute('id', $hotspot->getHtmlId())
        ->class('hotspot')
        ->attribute('style', "left: $hotspot->x%; top: $hotspot->y%")
        ->text((string)$hotspot->getVisibleAttribute('name'));
}
