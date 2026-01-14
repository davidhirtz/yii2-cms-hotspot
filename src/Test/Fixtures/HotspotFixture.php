<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Test\Fixtures;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Models\Asset;
use Hirtz\Cms\Test\Fixtures\AssetFixture;
use yii\test\ActiveFixture;

class HotspotFixture extends ActiveFixture
{
    public $depends = [AssetFixture::class];
    public $modelClass = Hotspot::class;

    public function afterLoad(): void
    {
        $hotspotCountByAssetId = [];

        foreach ($this->data as $data) {
            $hotspotCountByAssetId[$data['asset_id']]++;
        }

        foreach ($hotspotCountByAssetId as $assetId => $count) {
            $this->db->createCommand()
                ->update(Asset::tableName(), ['hotspot_count' => $count], ['id' => $assetId])
                ->execute();
        }

        parent::afterLoad();
    }
}
