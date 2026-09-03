<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\Actions;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Models\Actions\ReorderActiveRecords;
use davidhirtz\yii2\datetime\DateTime;
use Hirtz\Skeleton\Models\Trail;
use Override;
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

    #[Override]
    protected function afterReorder(): void
    {
        $trail = Trail::createOrderTrail($this->hotspot, Lang::t('hotspot', 'REORDER_HOTSPOT_ASSETS_HOTSPOT_ASSET_ORDER_CHANGED'));

        foreach ($this->hotspot->getTrailParents() as $parent) {
            Trail::createOrderTrail($parent, Lang::t('hotspot', 'REORDER_HOTSPOT_ASSETS_HOTSPOT_ASSET_ORDER_CHANGED'), [
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
