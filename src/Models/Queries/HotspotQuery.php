<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\Queries;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Skeleton\Db\I18nActiveQuery;

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
            'assets' => function (HotspotAssetQuery $query): void {
                $query->selectSiteAttributes()
                    ->replaceI18nAttributes()
                    ->whereStatus()
                    ->withFiles();
            },
        ]);
    }
}
