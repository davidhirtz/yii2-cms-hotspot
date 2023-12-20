<?php

namespace davidhirtz\yii2\cms\hotspot\models\builders;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\hotspot\modules\admin\Module;
use Yii;

class EntrySiteRelationsBuilder extends \davidhirtz\yii2\cms\models\builders\EntrySiteRelationsBuilder
{
    /**
     * @var Hotspot[]
     */
    public array $hotspots = [];

    /**
     * @var HotspotAsset[]
     */
    public array $hotspotAssets = [];

    protected array $hotspotIdsWithHotspotAssets = [];

    protected function loadAssets(): void
    {
        parent::loadAssets();

        if (!$this->assets) {
            return;
        }
        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('hotspot');
        $assetIdsWithHotspots = [];

        foreach ($this->assets as $asset) {
            if ($asset->getAttribute('hotspot_count')
                && ($asset->isSectionAsset() ? $module->enableSectionAssetHotspots : $module->enableEntryAssetHotspots)) {
                $assetIdsWithHotspots[] = $asset->id;
            }
        }

        if (!$assetIdsWithHotspots) {
            return;
        }

        Yii::debug('Loading related hotspots ...');

        $this->hotspots += Hotspot::find()
            ->selectSiteAttributes()
            ->replaceI18nAttributes()
            ->whereStatus()
            ->andWhere(['asset_id' => $assetIdsWithHotspots])
            ->indexBy('id')
            ->all();

        if (!$module->enableHotspotAssets) {
            return;
        }

        foreach ($this->hotspots as $hotspot) {
            if ($hotspot->asset_count) {
                $this->hotspotIdsWithHotspotAssets[] = $hotspot->id;
            }
        }

        if (!$this->hotspotIdsWithHotspotAssets) {
            return;
        }

        Yii::debug('Loading related hotspot assets ...');

        $this->hotspotAssets += HotspotAsset::find()
            ->selectSiteAttributes()
            ->replaceI18nAttributes()
            ->whereStatus()
            ->andWhere(['hotspot_id' => $this->hotspotIdsWithHotspotAssets])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->all();

        foreach ($this->hotspotAssets as $asset) {
            $this->fileIds[] = $asset->file_id;
        }
    }

    protected function populateAssetRelations(): void
    {
        foreach ($this->hotspotAssets as $hotspotAsset) {
            $hotspotAsset->populateFileRelation($this->files[$hotspotAsset->file_id] ?? null);
        }

        foreach ($this->hotspots as $hotspot) {
            $assets = array_filter($this->hotspotAssets, fn (HotspotAsset $hotspotAsset) => $hotspotAsset->hotspot_id == $hotspot->id);
            $hotspot->populateAssetRelations($assets);
        }

        foreach ($this->assets as $asset) {
            $hotspots = array_filter($this->hotspots, fn (Hotspot $hotspot) => $hotspot->asset_id == $asset->id);
            $asset->populateRelation('hotspots', $hotspots);
        }

        parent::populateAssetRelations();
    }
}
