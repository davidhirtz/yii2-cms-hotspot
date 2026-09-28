<?php

declare(strict_types=1);

/**
 * The default the `Artwork` widget falls back to while the page's own `widgets/_hotspots` does not exist: one link
 * per hotspot, placed by its percentages. A project styles `.hotspot` or supplies the view itself.
 *
 * @see \Hirtz\Cms\Hotspot\Widgets\Artwork::renderHotspots()
 * @var list<Hotspot> $hotspots
 */

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Skeleton\Helpers\Html;

foreach ($hotspots as $hotspot) {
    echo Html::tag($hotspot->link ? 'a' : 'span', Html::encode((string)$hotspot->name), [
        'id' => $hotspot->getHtmlId(),
        'class' => 'hotspot',
        'href' => $hotspot->link ?: null,
        'style' => "left: $hotspot->x%; top: $hotspot->y%",
    ]);
}
