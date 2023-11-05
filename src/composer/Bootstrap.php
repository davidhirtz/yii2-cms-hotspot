<?php

namespace davidhirtz\yii2\cms\hotspot\composer;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\models\builders\EntrySiteRelationsBuilder;
use davidhirtz\yii2\cms\models\ModelCloneEvent;
use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\hotspot\modules\admin\Module;
use davidhirtz\yii2\cms\modules\admin\widgets\grids\columns\AssetThumbnailColumn;
use davidhirtz\yii2\cms\widgets\Canvas;
use davidhirtz\yii2\media\modules\admin\widgets\forms\fields\AssetPreview;
use davidhirtz\yii2\skeleton\web\Application;
use yii\base\BootstrapInterface;
use Yii;
use yii\base\ModelEvent;
use yii\i18n\PhpMessageSource;

class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        Yii::setAlias('@hotspot', dirname(__DIR__));

        $app->extendComponent('i18n', [
            'translations' => [
                'hotspot' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@hotspot/messages',
                ],
            ],
        ]);

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'hotspot' => [
                        'class' => Module::class,
                    ],
                ],
            ],
            'media' => [
                'assets' => [
                    HotspotAsset::class,
                ],
            ],
        ]);

        Yii::$container->setDefinitions([
            AssetPreview::class => \davidhirtz\yii2\cms\hotspot\modules\admin\widgets\forms\fields\AssetPreview::class,
            AssetThumbnailColumn::class => \davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids\columns\AssetThumbnailColumn::class,
            Canvas::class => \davidhirtz\yii2\cms\hotspot\widgets\Canvas::class,
            EntrySiteRelationsBuilder::class => \davidhirtz\yii2\cms\hotspot\models\builders\EntrySiteRelationsBuilder::class,
        ]);

        // Makes sure hotspots (and their assets) are deleted on asset deletion
        ModelEvent::on(Asset::class, Asset::EVENT_BEFORE_DELETE, function (ModelEvent $event) {
            /** @var Asset $asset */
            $asset = $event->sender;

            if ($asset->getAttribute('hotspot_count')) {
                $hotspots = Hotspot::findAll(['asset_id' => $asset->id]);

                foreach ($hotspots as $hotspot) {
                    $hotspot->delete();
                }
            }
        });

        // Makes sure hotspots (and their assets) are cloned on asset, section or entry clone
        ModelEvent::on(Asset::class, Asset::EVENT_AFTER_CLONE, function (ModelCloneEvent $event) {
            /** @var Asset $clone */
            /** @var Asset $asset */
            $asset = $event->sender;
            $clone = $event->clone;

            if ($asset->getAttribute('hotspot_count')) {
                $hotspots = Hotspot::findAll(['asset_id' => $asset->id]);

                foreach ($hotspots as $hotspot) {
                    $hotspot->clone(['asset' => $clone]);
                }
            }
        });

        $app->setMigrationNamespace('davidhirtz\yii2\cms\hotspot\migrations');
    }
}