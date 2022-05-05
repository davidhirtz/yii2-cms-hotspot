<?php

namespace davidhirtz\yii2\cms\hotspot\models\queries;

use davidhirtz\yii2\skeleton\db\ActiveQuery;

/**
 * Class HotspotQuery
 * @package davidhirtz\yii2\cms\hotspot\models\queries
 */
class HotspotQuery extends ActiveQuery
{
    /**
     * @return $this
     */
    public function selectSiteAttributes()
    {
        return $this->addSelect($this->prefixColumns(array_diff($this->getModelInstance()->attributes(),
            ['updated_by_user_id', 'created_at'])));
    }

    /**
     * @return $this
     */
    public function withAssets()
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