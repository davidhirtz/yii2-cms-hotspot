<?php

namespace davidhirtz\yii2\cms\hotspot\models\actions;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\models\actions\ReorderActiveRecords;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\models\Trail;
use Yii;

class ReorderHotspotAssets extends ReorderActiveRecords
{
    public function __construct(protected Hotspot $hotspot, array $assetIds = [])
    {
        $assets = $hotspot->getAssets()
            ->select(['id', 'position'])
            ->andWhere(['id' => $assetIds])
            ->orderBy(['position' => SORT_ASC])
            ->all();

        $order = array_flip($assetIds);

        parent::__construct($assets, $order);
    }

    protected function afterReorder(): void
    {
        $trail = Trail::createOrderTrail($this->hotspot, Yii::t('hotspot', 'Hotspot asset order changed'));

        foreach ($this->hotspot->getTrailParents() as $parent) {
            Trail::createOrderTrail($parent, Yii::t('hotspot', 'Hotspot asset order changed'), [
                'trail_id' => $trail->id,
            ]);

            $parent->updated_at = new DateTime();
            $parent->update();
        }

        $this->hotspot->updated_at = new DateTime();
        $this->hotspot->update();

        parent::afterReorder();
    }

}