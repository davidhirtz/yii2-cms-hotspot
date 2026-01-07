<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\CmsSubmenu;
use Yii;

class HotspotSubmenu extends CmsSubmenu
{
    protected ?Hotspot $hotspot = null;

    public function hotspot(Hotspot $hotspot): static
    {
        $this->hotspot = $hotspot;
        return $this;
    }

    protected function configure(): void
    {
        $this->model ??= $this->hotspot->asset;
        $this->additionalActiveRoutes[$this->isEntryHotspot() ? 'entry' : 'sections'][] = 'admin/hotspot';

        parent::configure();
    }

    protected function setBreadcrumbs(): void
    {
        parent::setBreadcrumbs();

        $this->view->addBreadcrumb(Yii::t('cms', 'Asset'), [
            '/admin/asset/update',
            'id' => $this->hotspot->asset_id,
        ]);

        $this->view->addBreadcrumb(Yii::t('hotspot', 'Hotspot'), [
            '/admin/hotspot/update',
            'id' => $this->hotspot->id,
        ]);
    }

    protected function isEntryHotspot(): bool
    {
        return $this->hotspot->asset->isEntryAsset();
    }
}
