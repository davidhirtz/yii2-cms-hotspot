<?php

declare(strict_types=1);

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
    public function __construct(
        protected readonly DuplicateActiveRecordEvent $event,
        protected readonly Asset $asset,
        protected readonly Asset $duplicate,
    ) {
        $this->handleEvent();
    }

    public function handleEvent(): void
    {
        if ($this->asset->getAttribute('hotspot_count')) {
            Yii::debug('Duplicating hotspots ...');

            $hotspots = Hotspot::findAll(['asset_id' => $this->asset->id]);

            foreach ($hotspots as $hotspot) {
                DuplicateHotspot::create([
                    'hotspot' => $hotspot,
                    'asset' => $this->duplicate,
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
