<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot\widgets;

class Canvas extends \davidhirtz\yii2\cms\widgets\Canvas
{
    /**
     * @uses static::renderHotspots()
     */
    public string $template = '{media}{embed}{caption}{hotspots}';
    public string $hotspotViewFile = 'widgets/_hotspots';

    protected function renderHotspots(): string
    {
        if (!$this->asset->isAttributeVisible('#hotspots')) {
            return '';
        }

        $hotspots = $this->asset->getRelatedRecords()['hotspots'] ?? [];

        return $hotspots
            ? $this->getView()->render($this->hotspotViewFile, ['hotspots' => $hotspots], $this)
            : '';
    }
}
