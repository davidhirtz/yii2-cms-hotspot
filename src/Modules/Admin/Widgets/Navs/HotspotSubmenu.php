<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetSubmenuItem;
use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Hirtz\Skeleton\Widgets\Navs\Submenu;
use Override;

class HotspotSubmenu extends Submenu
{
    protected Hotspot $hotspot;

    public function hotspot(Hotspot $hotspot): static
    {
        $this->hotspot = $hotspot;
        return $this;
    }

    #[Override]
    protected function configure(): void
    {
        $this->addItem(
            $this->getAssetItem(),
            $this->getHotspotUpdateItem(),
            $this->getAssetsItem(),
        );

        parent::configure();
    }

    protected function getAssetItem(): ?NavItem
    {
        $asset = $this->hotspot->asset;

        return NavItem::make()
            ->icon('angle-double-left')
            ->label($asset->getAdminType())
            ->url($asset->getAdminRoute() ?: null);
    }

    protected function getHotspotUpdateItem(): ?NavItem
    {
        return NavItem::make()
            ->icon('cog')
            ->label($this->hotspot->getAdminType())
            ->routes(['admin/hotspot/hotspot/update'])
            ->url($this->hotspot->getAdminRoute() ?: null);
    }

    protected function getAssetsItem(): ?NavItem
    {
        return AssetSubmenuItem::make()
            ->badge($this->hotspot->asset_count)
            ->label($this->hotspot->getAttributeLabel('asset_count'))
            ->routes(['admin/hotspot/hotspot-asset'])
            ->url(HotspotAsset::getAdminIndexRoute($this->hotspot))
            ->visible($this->hotspot->hasAssetsEnabled());
    }
}
