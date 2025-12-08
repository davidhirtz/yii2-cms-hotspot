<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\models\events;

use Hirtz\Cms\hotspot\models\actions\DuplicateHotspot;
use Hirtz\Cms\hotspot\models\Hotspot;
use Hirtz\Cms\models\Asset;
use Hirtz\Skeleton\models\events\DuplicateActiveRecordEvent;
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
