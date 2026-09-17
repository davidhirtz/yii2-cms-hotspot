<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Models\BlockAsset;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\BlockHeader;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\EntryHeader;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\SectionHeader;
use Hirtz\Skeleton\Widgets\Navs\Header;
use Override;
use Stringable;

class HotspotHeader extends Header
{
    protected Hotspot $hotspot;

    public function hotspot(Hotspot $hotspot): static
    {
        $this->hotspot = $hotspot;
        return $this;
    }

    /**
     * The header of the asset the hotspot hangs on, which owns the title and the breadcrumbs — so whatever the view
     * added, the action dropdown above all, has to be handed on to it.
     */
    #[Override]
    protected function renderContent(): string|Stringable
    {
        $asset = $this->hotspot->asset;

        $header = match (true) {
            $asset instanceof BlockAsset => BlockHeader::make()->model($asset->model),
            $asset instanceof SectionAsset => SectionHeader::make()->model($asset->model),
            default => EntryHeader::make()->model($asset->model),
        };

        return $header->addContent(...$this->content);
    }
}
