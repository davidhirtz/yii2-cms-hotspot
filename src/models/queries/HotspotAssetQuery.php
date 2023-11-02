<?php

namespace davidhirtz\yii2\cms\hotspot\models\queries;

use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\media\models\queries\FileQuery;
use davidhirtz\yii2\skeleton\db\ActiveQuery;

/**
 * @method HotspotAsset[] all($db = null)
 * @method HotspotAsset[] each($batchSize = 100, $db = null)
 * @method HotspotAsset one($db = null)
 */
class HotspotAssetQuery extends ActiveQuery
{
    public function selectSiteAttributes(): static
    {
        return $this->addSelect($this->prefixColumns(array_diff($this->getModelInstance()->attributes(),
            ['updated_by_user_id', 'created_at'])));
    }

    public function withFiles(): static
    {
        return $this->with([
            'file' => function (FileQuery $query) {
                $query->selectSiteAttributes()
                    ->replaceI18nAttributes()
                    ->withFolder();
            }
        ]);
    }
}