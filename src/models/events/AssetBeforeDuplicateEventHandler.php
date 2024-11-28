<?php

namespace davidhirtz\yii2\cms\hotspot\models\events;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\skeleton\models\events\DuplicateActiveRecordEvent;

/**
 * @property DuplicateActiveRecordEvent $event
 */
class AssetBeforeDuplicateEventHandler
{
    public function __construct(
        protected readonly DuplicateActiveRecordEvent $event,
        protected readonly Asset $asset,
        protected readonly Asset $duplicate,
    ) {
        $this->handleEvent();
    }

    public function handleEvent(): void
    {
        $this->duplicate->setAttribute('hotspot_count', $this->asset->getAttribute('hotspot_count'));
    }
}
