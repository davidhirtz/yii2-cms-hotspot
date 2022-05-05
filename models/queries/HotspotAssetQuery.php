<?php

namespace davidhirtz\yii2\cms\hotspot\models\queries;

use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\media\models\queries\FileQuery;
use davidhirtz\yii2\skeleton\db\ActiveQuery;

/**
 * Class HotspotAssetQuery
 * @package davidhirtz\yii2\cms\hotspot\models\queries
 *
 * @method HotspotAsset[] all($db = null)
 * @method HotspotAsset one($db = null)
 */
class HotspotAssetQuery extends ActiveQuery
{
    /**
     * @return HotspotAssetQuery
     */
    public function selectSiteAttributes()
    {
        return $this->addSelect($this->prefixColumns(array_diff($this->getModelInstance()->attributes(),
            ['updated_by_user_id', 'created_at'])));
    }

    /**
     * @return HotspotAssetQuery
     */
    public function withFiles()
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