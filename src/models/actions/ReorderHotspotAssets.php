<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\models\actions;

use Hirtz\Cms\hotspot\models\Hotspot;
use Hirtz\Cms\Models\actions\ReorderActiveRecords;
use davidhirtz\yii2\datetime\DateTime;
use Hirtz\Skeleton\Models\Trail;
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

    #[\Override]
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
