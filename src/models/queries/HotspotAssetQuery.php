<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\models\queries;

use Hirtz\Cms\hotspot\models\HotspotAsset;
use Hirtz\Media\models\queries\FileQuery;
use Hirtz\Skeleton\Db\I18nActiveQuery;

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
