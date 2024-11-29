<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot\models\queries;

use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\media\models\queries\FileQuery;
use davidhirtz\yii2\skeleton\db\I18nActiveQuery;

/**
 * @extends I18nActiveQuery<HotspotAsset>
 */
class HotspotAssetQuery extends I18nActiveQuery
{
    public function selectSiteAttributes(): self
    {
        return $this->addSelect($this->prefixColumns(array_diff($this->getModelInstance()->attributes(), [
            'updated_by_user_id',
            'created_at',
        ])));
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
