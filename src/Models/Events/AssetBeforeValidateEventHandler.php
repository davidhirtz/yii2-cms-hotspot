<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\Events;

use Hirtz\Media\Models\Asset;
use Yii;

/**
 * Hotspots are placed on the image, so an asset carrying them keeps a file that has one.
 */
readonly class AssetBeforeValidateEventHandler
{
    public function __construct(protected Asset $asset)
    {
        $this->handleEvent();
    }

    protected function handleEvent(): void
    {
        if ($this->asset->getIsNewRecord()
            || !$this->asset->isAttributeChanged('file_id')
            || !$this->asset->getAttribute('hotspot_count')
            || $this->asset->file?->hasPreview() !== false) {
            return;
        }

        $this->asset->addError('file_id', Yii::t('hotspot', 'HOTSPOT_FILE_PREVIEW_ERROR'));
    }
}
