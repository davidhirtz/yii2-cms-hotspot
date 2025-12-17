<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\events;

use Hirtz\Cms\Hotspot\Models\Actions\DuplicateHotspot;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Models\Asset;
use Hirtz\Skeleton\Models\Events\DuplicateActiveRecordEvent;
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
