<?php

namespace davidhirtz\yii2\cms\hotspot\controllers;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\media\Module;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @property Module $module
 * @noinspection PhpUnused
 */
class SiteController extends \davidhirtz\yii2\cms\controllers\SiteController
{
    public function actionView(string $entry): Response|string
    {
        $entry = $this->getQuery()
            ->whereSlug($entry)
            ->withSections()
            ->limit(1)
            ->one();

        if (!$entry || !$entry->getRoute()) {
            throw new NotFoundHttpException();
        }

        $entry->populateAssetRelations($this->findAssetsForEntry($entry));

        return $this->render('view', [
            'entry' => $entry,
        ]);
    }

    /**
     * Loads assets for entry and (if needed) related sections without file relations. Then check if any assets contain
     * hotspots. If found, the hotspots and (again, only if needed) their hotspot assets will be loaded. Finally, all
     * files found in both assets and hotspot assets are loaded with their folders and are populated in their related
     * records.
     *
     * @param Entry $entry
     * @return Asset[]
     */
    protected function findAssetsForEntry(Entry $entry): array
    {
        $query = Asset::find()
            ->selectSiteAttributes()
            ->replaceI18nAttributes()
            ->andWhere(['entry_id' => $entry->id])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->whereStatus();

        if (!$entry->isRelationPopulated('sections')) {
            $query->andWhere(['section_id' => null]);
        }

        $assets = $query->all();
        $assetIdsWithHotspots = [];
        $fileIds = [];

        foreach ($assets as $asset) {
            if ($asset->getAttribute('hotspot_count')) {
                $assetIdsWithHotspots[] = $asset->id;
            }

            $fileIds[] = $asset->file_id;
        }

        if ($assetIdsWithHotspots) {
            $hotspots = Hotspot::find()
                ->selectSiteAttributes()
                ->replaceI18nAttributes()
                ->andWhere(['asset_id' => $assetIdsWithHotspots])
                ->withAssets()
                ->whereStatus()
                ->all();

            /** @var Hotspot[] $hotspots */
            foreach ($hotspots as $hotspot) {
                foreach ($hotspot->assets as $hotspotAsset) {
                    $fileIds[] = $hotspotAsset->file_id;
                }
            }

            // Populate hotspot relations on all assets.
            foreach ($assets as $asset) {
                $asset->populateRelation('hotspots', array_filter($hotspots, function (Hotspot $hotspot) use ($asset) {
                    return $hotspot->asset_id == $asset->id;
                }));
            }
        }

        if ($fileIds) {
            $files = File::find()
                ->selectSiteAttributes()
                ->replaceI18nAttributes()
                ->where(['id' => array_unique($fileIds)])
                ->withFolder()
                ->indexBy('id')
                ->all();

            foreach ($assets as $asset) {
                $asset->populateFileRelation($files[$asset->file_id] ?? null);

                /** @var Hotspot $hotspot */
                foreach ($asset->getRelatedRecords()['hotspots'] ?? [] as $hotspot) {
                    foreach ($hotspot->assets as $hotspotAsset) {
                        $hotspotAsset->populateFileRelation($files[$hotspotAsset->file_id] ?? null);
                    }
                }
            }
        }

        return $assets;
    }
}