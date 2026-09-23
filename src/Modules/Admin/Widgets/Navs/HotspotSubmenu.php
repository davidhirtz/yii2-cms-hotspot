<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetSubmenuItem;
use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Hirtz\Skeleton\Widgets\Navs\Submenu;
use Hirtz\Skeleton\Widgets\Traits\ModelTrait;
use Override;

class HotspotSubmenu extends Submenu
{
    /**
     * @use ModelTrait<Hotspot>
     */
    use ModelTrait;

    #[Override]
    protected function configure(): void
    {
        $this->addItem(
            hotspot: $this->getHotspotUpdateItem(),
            assets: $this->getAssetsItem(),
        );

        parent::configure();
    }

    protected function getHotspotUpdateItem(): ?NavItem
    {
        return NavItem::make()
            ->icon('cog')
            ->label($this->model->getAdminType())
            ->addRoute('admin/hotspot/hotspot/update')
            ->url($this->model->getAdminRoute() ?: null);
    }

    protected function getAssetsItem(): ?NavItem
    {
        return AssetSubmenuItem::make()
            ->badge($this->model->asset_count)
            ->label($this->model->getAttributeLabel('asset_count'))
            ->addRoute('admin/hotspot/hotspot-asset')
            ->url(HotspotAsset::getAdminIndexRoute($this->model))
            ->visible($this->model->allowsAssets());
    }
}
