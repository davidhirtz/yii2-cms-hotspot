<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot\models\events;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\models\Asset;
use Yii;
use yii\base\ModelEvent;

class AssetBeforeDeleteEventHandler
{
    public function __construct(protected readonly ModelEvent $event, protected readonly Asset $asset)
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
