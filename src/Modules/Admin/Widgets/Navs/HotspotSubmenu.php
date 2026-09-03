<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Cms\Hotspot\Models\Hotspot;
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

    #[\Override]
    protected function setBreadcrumbs(): void
    {
        parent::setBreadcrumbs();

        $this->view->addBreadcrumb(Lang::t('cms', 'COMMON_ASSET'), [
            '/admin/asset/update',
            'id' => $this->hotspot->asset_id,
        ]);

        $this->view->addBreadcrumb(Lang::t('hotspot', 'COMMON_HOTSPOT'), [
            '/admin/hotspot/update',
            'id' => $this->hotspot->id,
        ]);
    }

    protected function isEntryHotspot(): bool
    {
        return $this->hotspot->asset->isEntryAsset();
    }
}
