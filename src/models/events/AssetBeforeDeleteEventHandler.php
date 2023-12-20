<?php

namespace davidhirtz\yii2\cms\hotspot\models\events;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\models\Asset;
use Yii;
use yii\base\ModelEvent;

class AssetBeforeDeleteEventHandler
{
    public function __construct(protected ModelEvent $event)
    {
        $this->handleEvent();
    }

    protected function handleEvent(): void
    {
        /** @var Asset $asset */
        $asset = $this->event->sender;

        if ($asset->getAttribute('hotspot_count')) {
            Yii::debug('Deleting hotspots before deleting asset ...', __METHOD__);

            $hotspots = Hotspot::findAll(['asset_id' => $asset->id]);

            foreach ($hotspots as $hotspot) {
                $hotspot->delete();
            }
        }
    }
}
