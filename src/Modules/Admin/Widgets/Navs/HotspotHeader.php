<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\FrontendLink;
use Hirtz\Skeleton\Widgets\Navs\ModelHeader;
use Override;

/**
 * @extends ModelHeader<Hotspot>
 */
class HotspotHeader extends ModelHeader
{
    #[Override]
    protected function configure(): void
    {
        $this->subheading ??= FrontendLink::findInChain($this->model)?->addClass('hidden-sticky');

        parent::configure();
    }
}
