<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Events;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\ModuleTrait;
use Hirtz\Cms\Models\Actions\PreloadEntrySiteRelations;
use Hirtz\Cms\Models\Events\EntrySiteRelationsEvent;
use Yii;

class HotspotEntrySiteRelationsEventHandler
{
    use ModuleTrait;

    /**
     * One instance serves every preload of the process, so nothing of a preload is kept on it.
     */
    public function __invoke(EntrySiteRelationsEvent $event): void
    {
        if (!$event->sender->assets) {
            return;
        }

        $module = static::getModule();
        $assetIdsWithHotspots = [];

        foreach ($event->sender->assets as $asset) {
            if ($asset->getAttribute('hotspot_count') && $module->allowsHotspots($asset)) {
                $assetIdsWithHotspots[] = $asset->id;
            }
        }

        if (!$assetIdsWithHotspots) {
            return;
        }

        Yii::debug('Loading related hotspots ...');

        $hotspots = Hotspot::find()
            ->selectSiteAttributes()
            ->withTranslations()
            ->whereStatus()
            ->andWhere(['asset_id' => $assetIdsWithHotspots])
            ->indexBy('id')
            ->all();

        foreach ($event->sender->assets as $asset) {
            if (in_array($asset->id, $assetIdsWithHotspots, true)) {
                $related = array_filter($hotspots, fn (Hotspot $hotspot): bool => $hotspot->asset_id === $asset->id);
                $asset->populateRelation('hotspots', $related);
            }
        }

        if (!$module->enableHotspotAssets) {
            return;
        }

        $hotspotIdsWithHotspotAssets = [];

        foreach ($hotspots as $hotspot) {
            if ($hotspot->asset_count) {
                $hotspotIdsWithHotspotAssets[] = $hotspot->id;
            }
        }

        if (!$hotspotIdsWithHotspotAssets) {
            foreach ($hotspots as $hotspot) {
                $hotspot->populateAssetRelations([]);
            }

            return;
        }

        Yii::debug('Loading related hotspot assets ...');

        $hotspotAssets = HotspotAsset::find()
            ->selectSiteAttributes()
            ->whereStatus()
            ->andWhere(['model_id' => $hotspotIdsWithHotspotAssets])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->all();

        foreach ($hotspotAssets as $asset) {
            $event->sender->fileIds[] = $asset->file_id;
        }

        $event->sender->on(PreloadEntrySiteRelations::EVENT_AFTER_LOAD_FILES, function () use ($event, $hotspots, $hotspotAssets): void {
            foreach ($hotspotAssets as $hotspotAsset) {
                $hotspotAsset->populateFileRelation($event->sender->files[$hotspotAsset->file_id] ?? null);
            }

            foreach ($hotspots as $hotspot) {
                $hotspot->populateAssetRelations($hotspotAssets);
            }
        });
    }
}
