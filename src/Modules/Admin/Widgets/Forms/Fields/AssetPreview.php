<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms\Fields;

use Hirtz\Cms\Hotspot\Assets\HotspotAdminAssetBundle;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Module;
use Hirtz\Cms\Models\Asset;
use Yii;
use yii\helpers\Url;

class AssetPreview extends \Hirtz\Media\Modules\Admin\Widgets\Forms\Fields\AssetPreview
{
    protected function configure(): void
    {
        if ($this->asset->file->hasPreview() && $this->hasHotspotsEnabled()) {
            $this->registerClientScript();
        }

        parent::configure();
    }

    protected function hasHotspotsEnabled(): bool
    {
        if (!$this->asset instanceof Asset) {
            return false;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('hotspot');

        return $this->asset->isSectionAsset()
            ? $module->enableSectionAssetHotspots
            : $module->enableEntryAssetHotspots;
    }

    protected function registerClientScript(): void
    {
        $hotspots = $this->getHotspots();

        $this->view->registerJsModule(HotspotAdminAssetBundle::class, [
            'formName' => Hotspot::instance()->formName(),
            'url' => Url::toRoute(['/admin/hotspot/create', 'id' => $this->asset->id]),
            'message' => !$hotspots ? Yii::t('hotspot', 'Double click on the image to create a hotspot.') : null,
            'hotspots' => $hotspots,
        ]);
    }

    /**
     * @return Hotspot[]
     */
    protected function getHotspots(): array
    {
        if (!$this->asset->isRelationPopulated('hotspots')) {
            $this->asset->populateRelation('hotspots', $this->asset->getAttribute('hotspot_count')
                ? Hotspot::find()
                    ->where(['asset_id' => $this->asset->id])
                    ->orderBy(['position' => SORT_ASC])
                    ->all()
                : []);
        }

        return $this->asset->getRelatedRecords()['hotspots'] ?? [];
    }
}
