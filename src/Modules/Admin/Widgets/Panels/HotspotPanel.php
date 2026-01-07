<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Panels;

use Hirtz\Media\Modules\Admin\Widgets\Panels\Traits\DuplicateButtonTrait;
use Hirtz\Skeleton\Widgets\Panels\Panel;
use Hirtz\Skeleton\Widgets\Traits\ModelWidgetTrait;
use Hirtz\Skeleton\Widgets\Widget;
use Stringable;

class HotspotPanel extends Widget
{
    use DuplicateButtonTrait;
    use ModelWidgetTrait;

    protected function renderContent(): string|Stringable
    {
        return Panel::make()
            ->attribute('id', 'operations')
            ->buttons(...$this->getButtons());
    }

    protected function getButtons(): array
    {
        return [$this->getDuplicateButton()];
    }
}
