<?php

namespace davidhirtz\yii2\cms\hotspot\widgets;

class Canvas extends \davidhirtz\yii2\cms\widgets\Canvas
{
    /**
     * @uses static::renderHotspots()
     */
    public string $template = '{media}{embed}{caption}{hotspots}';
    public string $hotspotViewFile = '_hotspots';

    protected function renderHotspots(): string
    {
        if (!$this->asset->isAttributeVisible('#hotspots')
            || (!$hotspots = $this->asset->getRelatedRecords()['hotspots'])) {
            return '';
        }

        return $this->getView()->render($this->hotspotViewFile, [
            'hotspots' => $hotspots,
        ]);
    }
}