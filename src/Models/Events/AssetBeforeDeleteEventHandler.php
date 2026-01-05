<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\Events;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Models\Asset;
use Yii;
use yii\base\ModelEvent;

readonly class AssetBeforeDeleteEventHandler
{
    public function __construct(protected ModelEvent $event, protected Asset $asset)
    {
        $this->handleEvent();
    }

    protected function handleEvent(): void
    {
        if ($this->asset->getAttribute('hotspot_count')) {
            Yii::debug('Deleting hotspots before deleting asset ...', __METHOD__);

            $hotspots = Hotspot::findAll(['asset_id' => $this->asset->id]);

            foreach ($hotspots as $hotspot) {
                $hotspot->delete();
            }
        }
    }
}
