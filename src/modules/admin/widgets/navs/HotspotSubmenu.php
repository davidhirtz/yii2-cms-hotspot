<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\navs;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\modules\admin\widgets\navs\Submenu;
use Yii;

class HotspotSubmenu extends Submenu
{
    public ?Hotspot $hotspot = null;

    public function init(): void
    {
        $this->model ??= $this->hotspot->asset;
        $this->additionalActiveRoutes[$this->isEntryHotspot() ? 'entry' : 'sections'][] = 'admin/hotspot';

        parent::init();
    }

    protected function setBreadcrumbs(): void
    {
        parent::setBreadcrumbs();

        $this->getView()->setBreadcrumbs([
            Yii::t('cms', 'Asset') => ['/admin/asset/update', 'id' => $this->hotspot->asset_id],
            Yii::t('hotspot', 'Hotspot') => ['/admin/hotspot/update', 'id' => $this->hotspot->id],
        ]);
    }

    protected function isEntryHotspot(): bool
    {
        return $this->hotspot->asset->isEntryAsset();
    }
}
