<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\EntryHeader;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\SectionHeader;
use Hirtz\Skeleton\Widgets\Widget;
use Stringable;

class HotspotHeader extends Widget
{
    protected Hotspot $hotspot;

    public function hotspot(Hotspot $hotspot): static
    {
        $this->hotspot = $hotspot;
        return $this;
    }

    protected function renderContent(): string|Stringable
    {
        return $this->hotspot->asset instanceof SectionAsset
            ? SectionHeader::make()
                ->model($this->hotspot->asset->model)
            : EntryHeader::make()
                ->model($this->hotspot->asset->model);
    }
}
