<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\Models\Queries;

use Hirtz\Cms\hotspot\Models\HotspotAsset;
use Hirtz\Media\Models\Queries\FileQuery;
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
            'file' => function (FileQuery $query): void {
                $query->selectSiteAttributes()
                    ->replaceI18nAttributes()
                    ->withFolder();
            }
        ]);
    }
}
