<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids;

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Models\Asset;
use Hirtz\Media\Modules\Admin\Widgets\Grids\Traits\AssetGridViewTrait;
use Hirtz\Media\Traits\FilePropertyTrait;
use Hirtz\Skeleton\Widgets\Grids\Columns\BadgeColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\ButtonColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\ViewGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\Columns\LinkColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\RelativeTimeColumn;
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Hirtz\Skeleton\Widgets\Icon;
use Override;
use Stringable;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * @extends GridView<HotspotAsset>
 */
class FileHotspotAssetGrid extends GridView
{
    use AssetGridViewTrait;
    use FilePropertyTrait;

    protected string $language;

    protected string $layout = '{items}{pager}';

    public function language(string $language): static
    {
        $this->language = $language;
        return $this;
    }

    #[Override]
    protected function configure(): void
    {
        Yii::$app->getI18n()->callback($this->language, function (): void {
            $this->provider ??= new ActiveDataProvider([
                'query' => HotspotAsset::find()
                    ->where(['file_id' => $this->file->id])
                    ->with(['hotspot'])
                    ->orderBy(['updated_at' => SORT_DESC]),
            ]);

            $this->provider->getPagination()->pageParam = "hotspot-asset-page-$this->language";

            /** @var Asset $asset */
            foreach ($this->provider->getModels() as $asset) {
                $asset->populateRelation('file', $this->file);
            }
        });

        $this->columns ??= [
            $this->getStatusColumn(),
            $this->getTypeColumn(),
            $this->getNameColumn(),
            $this->getAssetCountColumn(),
            $this->getUpdatedAtColumn(),
            $this->getButtonColumn(),
        ];

        parent::configure();
    }

    protected function getStatusColumn(): Column
    {
        return LinkColumn::make()
            ->property('status')
            ->title(false)
            ->content($this->getStatusIcon(...))
            ->url(fn (HotspotAsset $asset) => $asset->getAdminRoute())
            ->centered();
    }

    protected function getStatusIcon(HotspotAsset $asset): Stringable
    {
        return Icon::make()
            ->name($asset->parent->getStatusIcon())
            ->tooltip($asset->parent->getStatusName());
    }

    protected function getTypeColumn(): Column
    {
        return LinkColumn::make()
            ->property('type')
            ->content($this->getTypeColumnContent(...))
            ->url(fn (HotspotAsset $asset) => $this->getParentRoute($asset));
    }

    protected function getTypeColumnContent(HotspotAsset $asset): string|Stringable
    {
        return $asset->hotspot->getTypeName();
    }

    protected function getNameColumn(): Column
    {
        return LinkColumn::make()
            ->property('name')
            ->content($this->getNameColumnContent(...))
            ->url(fn (HotspotAsset $asset) => $asset->getAdminRoute());
    }

    protected function getNameColumnContent(HotspotAsset $asset): string|Stringable
    {
        return $asset->hotspot->getDisplayName();
    }

    protected function getAssetCountColumn(): ?Column
    {
        return BadgeColumn::make()
            ->property('asset_count')
            ->content($this->getAssetCountColumnContent(...))
            ->url(fn (HotspotAsset $asset) => [...$this->getParentRoute($asset), '#' => 'assets']);
    }

    protected function getAssetCountColumnContent(HotspotAsset $asset): string
    {
        return (string)$asset->parent->asset_count;
    }

    protected function getUpdatedAtColumn(): ?Column
    {
        return RelativeTimeColumn::make()
            ->property('updated_at');
    }

    protected function getButtonColumn(): ?Column
    {
        return ButtonColumn::make()
            ->content($this->getButtonColumnContent(...));
    }

    protected function getButtonColumnContent(HotspotAsset $asset): array
    {
        return [
            ViewGridButton::make()
                ->url($this->getParentRoute($asset)),
        ];

    }

    protected function getParentRoute(HotspotAsset $asset): array|false
    {
        return $this->getI18nRoute([...$asset->hotspot->getAdminRoute(), '#' => "asset-$asset->id"]);
    }

    protected function getI18nRoute(array $route): array
    {
        return [
            ...$route,
            'language' => $this->language !== Yii::$app->language ? $this->language : null,
        ];
    }
}
