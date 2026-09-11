<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Models\EntryAsset;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\EntrySubmenu;
use Yii;

class HotspotSubmenu extends EntrySubmenu
{
    protected ?Hotspot $hotspot = null;

    public function hotspot(Hotspot $hotspot): static
    {
        $this->hotspot = $hotspot;
        return $this;
    }

    #[\Override]
    protected function configure(): void
    {
        $this->model ??= $this->hotspot->asset;
        $this->additionalActiveRoutes[$this->isEntryHotspot() ? 'entry' : 'sections'][] = 'admin/hotspot';

        parent::configure();
    }

    protected function isEntryHotspot(): bool
    {
        return $this->hotspot->asset instanceof EntryAsset;
    }
}
