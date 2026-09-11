<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Models\EntryAsset;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\EntrySubmenu;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\SectionSubmenu;
use Hirtz\Skeleton\Html\Traits\TagContentTrait;
use Hirtz\Skeleton\Widgets\Widget;
use Override;
use Stringable;
use yii\base\InvalidCallException;

/**
 * The one place that still has to know whether the hotspot sits on an entry or a section asset: it borrows that
 * record's submenu and marks the hotspot routes active on its assets item.
 */
class HotspotSubmenu extends Widget
{
    use TagContentTrait;

    protected Hotspot $hotspot;

    public function hotspot(Hotspot $hotspot): static
    {
        $this->hotspot = $hotspot;
        return $this;
    }

    #[Override]
    protected function renderContent(): string|Stringable
    {
        $asset = $this->hotspot->asset;

        $submenu = match (true) {
            $asset instanceof EntryAsset => EntrySubmenu::make()->model($asset->model),
            $asset instanceof SectionAsset => SectionSubmenu::make()->model($asset->model),
            default => throw new InvalidCallException($asset::class . ' cannot carry hotspots.'),
        };

        return $submenu
            ->additionalActiveRoutes([
                'assets' => ['admin/hotspot/hotspot', 'admin/hotspot/hotspot-asset'],
            ])
            ->content(...$this->content);
    }
}
