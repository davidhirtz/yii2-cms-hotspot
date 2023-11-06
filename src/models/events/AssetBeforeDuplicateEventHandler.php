<?php

namespace davidhirtz\yii2\cms\hotspot\models\events;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\skeleton\models\events\DuplicateActiveRecordEvent;

/**
 * @property DuplicateActiveRecordEvent{duplicate: Asset, sender: Asset} $event
 */
class AssetBeforeDuplicateEventHandler
{
    public function __construct(protected DuplicateActiveRecordEvent $event)
    {
        $this->handleEvent();
    }

    public function handleEvent(): void
    {
        $this->event->duplicate->setAttribute('hotspot_count', $this->event->sender->getAttribute('hotspot_count'));
    }
}