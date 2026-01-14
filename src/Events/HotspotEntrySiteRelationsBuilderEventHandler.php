<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Events;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Module;
use Hirtz\Cms\Models\Builders\EntrySiteRelationsBuilder;
use Hirtz\Cms\Models\Events\EntrySiteRelationsBuilderEvent;
use Yii;

class HotspotEntrySiteRelationsBuilderEventHandler
{
    /**
     * @var Hotspot[]
     */
    private array $hotspots = [];

    /**
     * @var HotspotAsset[]
     */
    private array $hotspotAssets = [];

    /**
     * @var int[]
     */
    private array $hotspotIdsWithHotspotAssets = [];

    public function __invoke(EntrySiteRelationsBuilderEvent $event): void
    {
        if (!$event->sender->assets) {
            return;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('hotspot');
        $assetIdsWithHotspots = [];

        foreach ($event->sender->assets as $asset) {
            if (
                $asset->getAttribute('hotspot_count')
                && ($asset->isSectionAsset() ? $module->enableSectionAssetHotspots : $module->enableEntryAssetHotspots)
            ) {
                $assetIdsWithHotspots[] = $asset->id;
            }
        }

        if (!$assetIdsWithHotspots) {
            return;
        }

        Yii::debug('Loading related hotspots ...');

        $this->hotspots = Hotspot::find()
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

        $this->hotspotAssets = HotspotAsset::find()
            ->selectSiteAttributes()
            ->replaceI18nAttributes()
            ->whereStatus()
            ->andWhere(['hotspot_id' => $this->hotspotIdsWithHotspotAssets])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->all();

        foreach ($this->hotspotAssets as $asset) {
            $event->sender->fileIds[] = $asset->file_id;
        }

        $event->sender->on(EntrySiteRelationsBuilder::EVENT_AFTER_LOAD_FILES, function () use ($event): void {
            foreach ($this->hotspotAssets as $hotspotAsset) {
                $hotspotAsset->populateFileRelation($this->files[$hotspotAsset->file_id] ?? null);
            }

            foreach ($this->hotspots as $hotspot) {
                $assets = array_filter($this->hotspotAssets, fn (HotspotAsset $hotspotAsset) => $hotspotAsset->hotspot_id == $hotspot->id);

                foreach ($assets as $asset) {
                    $asset->populateRelation('hotspot', $hotspot);
                }

                $hotspot->populateAssetRelations($assets);
            }

            foreach ($event->sender->assets as $asset) {
                $hotspots = array_filter($this->hotspots, fn (Hotspot $hotspot) => $hotspot->asset_id == $asset->id);
                $asset->populateRelation('hotspots', $hotspots);
            }
        });
    }
}
