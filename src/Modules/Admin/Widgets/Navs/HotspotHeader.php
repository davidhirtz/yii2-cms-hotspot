<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetHeader;
use Override;

class HotspotHeader extends AssetHeader
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
        $this->model ??= $this->hotspot->asset;
        $this->subtitle ??= $this->hotspot->getAdminName();

        parent::configure();
    }
}
