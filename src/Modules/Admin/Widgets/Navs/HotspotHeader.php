<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetHeader;
use Override;

/**
 * A hotspot has no page of its own to lead back to, so the header is the one of the asset it sits on and the
 * hotspot only names itself in the subtitle.
 */
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
