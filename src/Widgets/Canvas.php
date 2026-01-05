<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Widgets;

class Canvas extends \Hirtz\Cms\Widgets\Canvas
{
    public string $layout = '{media}{embed}{caption}{hotspots}';
    public string $hotspotViewFile = 'widgets/_hotspots';

    protected function renderHotspots(): string
    {
        if (!$this->asset->isAttributeVisible('#hotspots')) {
            return '';
        }

        $hotspots = $this->asset->getRelatedRecords()['hotspots'] ?? [];

        return $hotspots
            ? $this->view->render($this->hotspotViewFile, ['hotspots' => $hotspots], $this)
            : '';
    }
}
