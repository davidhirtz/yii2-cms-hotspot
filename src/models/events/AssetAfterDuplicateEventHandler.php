<?php

namespace davidhirtz\yii2\cms\hotspot\models\events;

use davidhirtz\yii2\cms\hotspot\models\actions\DuplicateHotspot;
use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\skeleton\models\events\DuplicateActiveRecordEvent;
use Yii;

/**
 * @property DuplicateActiveRecordEvent $event
 */
class AssetAfterDuplicateEventHandler
{
    public function __construct(protected DuplicateActiveRecordEvent $event)
    {
        $this->handleEvent();
    }

    public function handleEvent(): void
    {
        /** @var Asset $asset */
        $asset = $this->event->sender;

        if ($asset->getAttribute('hotspot_count')) {
            Yii::debug('Duplicating hotspots ...');

            $hotspots = Hotspot::findAll(['asset_id' => $asset->id]);

            foreach ($hotspots as $hotspot) {
                DuplicateHotspot::create([
                    'hotspot' => $hotspot,
                    'asset' => $this->event->duplicate,
                    'shouldUpdateAssetAfterInsert' => false,
                    'attributes' => [
                        'status' => $hotspot->status,
                        'position' => $hotspot->position,
                    ],
                ]);
            }
        }
    }
}
