<?php

declare(strict_types=1);

/**
 * The fallback while the project's own `widgets/_hotspots` does not exist; a project styles `.hotspot` or replaces it.
 *
 * @see \Hirtz\Cms\Hotspot\Widgets\Artwork::renderHotspots()
 * @var list<Hotspot> $hotspots
 */

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Skeleton\Helpers\Html;

foreach ($hotspots as $hotspot) {
    $link = $hotspot->getVisibleAttribute('link');

    echo Html::tag($link ? 'a' : 'span', Html::encode((string)$hotspot->getVisibleAttribute('name')), [
        'id' => $hotspot->getHtmlId(),
        'class' => 'hotspot',
        'href' => $link ?: null,
        'style' => "left: $hotspot->x%; top: $hotspot->y%",
    ]);
}
