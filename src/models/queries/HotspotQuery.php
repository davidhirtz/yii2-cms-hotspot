<?php

namespace davidhirtz\yii2\cms\hotspot\models\queries;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\skeleton\db\ActiveQuery;

/**
 * @method Hotspot[] all($db = null)
 * @method Hotspot[] each($batchSize = 100, $db = null)
 * @method Hotspot one($db = null)
 */
class HotspotQuery extends ActiveQuery
{
    public function selectSiteAttributes(): static
    {
        return $this->addSelect($this->prefixColumns(array_diff($this->getModelInstance()->attributes(),
            ['updated_by_user_id', 'created_at'])));
    }

    public function withAssets(): static
    {
        return $this->with([
            'assets' => function (HotspotAssetQuery $query) {
                $query->selectSiteAttributes()
                    ->replaceI18nAttributes()
                    ->whereStatus()
                    ->withFiles();
            },
        ]);
    }
}