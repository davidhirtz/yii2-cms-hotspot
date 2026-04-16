<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Panels;

use Hirtz\Skeleton\Widgets\Buttons\DuplicateButton;
use Hirtz\Skeleton\Widgets\Panels\Panel;
use Hirtz\Skeleton\Widgets\Traits\ModelTrait;
use Hirtz\Skeleton\Widgets\Widget;
use Stringable;

class HotspotPanel extends Widget
{
    use ModelTrait;

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

    protected function getDuplicateButton(): ?Stringable
    {
        return DuplicateButton::make()->model($this->model);
    }
}
