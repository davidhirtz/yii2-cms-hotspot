<?php

namespace davidhirtz\yii2\cms\hotspot\models\queries;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\skeleton\db\I18nActiveQuery;

/**
 * @extends I18nActiveQuery<Hotspot>
 */
class HotspotQuery extends I18nActiveQuery
{
    public function selectSiteAttributes(): static
    {
        return $this->addSelect($this->prefixColumns(array_diff($this->getModelInstance()->attributes(), [
            'updated_by_user_id',
            'created_at',
        ])));
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
