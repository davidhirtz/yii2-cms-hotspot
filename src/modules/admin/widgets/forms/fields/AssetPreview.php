<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\forms\fields;

use davidhirtz\yii2\cms\hotspot\assets\AdminAsset;
use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\modules\admin\Module;
use davidhirtz\yii2\cms\models\Asset;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;

class AssetPreview extends \davidhirtz\yii2\media\modules\admin\widgets\forms\fields\AssetPreview
{
    public function init(): void
    {
        if ($this->asset->file->hasPreview() && $this->hasHotspotsEnabled()) {
            $this->registerClientScripts();
        }

        parent::init();
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

    protected function registerClientScripts(): void
    {
        $hotspots = $this->getHotspots();

        $options = array_filter([
            'formName' => Hotspot::instance()->formName(),
            'url' => Url::toRoute(['/admin/hotspot/create', 'id' => $this->asset->id]),
            'message' => !$hotspots ? Yii::t('hotspot', 'Double click on the image to create a hotspot.') : null,
            'hotspots' => $hotspots,
        ]);

        AdminAsset::register($view = Yii::$app->getView());
        $view->registerJs('Skeleton.registerHotspots(' . Json::htmlEncode($options) . ')');
    }

    /**
     * @return Hotspot[]
     */
    protected function getHotspots(): array
    {
        if (!$this->asset->isRelationPopulated('hotspots')) {
            if ($this->asset->getAttribute('hotspot_count')) {
                $hotspots = Hotspot::find()
                    ->where(['asset_id' => $this->asset->id])
                    ->orderBy(['position' => SORT_ASC])
                    ->all();
            }

            $this->asset->populateRelation('hotspots', $hotspots ?? []);
        }

        return $this->asset->getRelatedRecords()['hotspots'] ?? [];
    }
}
