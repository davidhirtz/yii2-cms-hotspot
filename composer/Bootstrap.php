<?php

namespace davidhirtz\yii2\cms\hotspot\composer;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\models\base\ModelCloneEvent;
use davidhirtz\yii2\cms\hotspot\assets\AdminAsset;
use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\modules\admin\widgets\forms\AssetActiveForm;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\hotspot\modules\admin\Module;
use davidhirtz\yii2\skeleton\web\Application;
use yii\base\BootstrapInterface;
use Yii;
use yii\base\ModelEvent;
use yii\base\WidgetEvent;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Class Bootstrap
 * @package davidhirtz\yii2\cms\hotspot\bootstrap
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@hotspot', dirname(__DIR__));

        $app->extendComponent('i18n', [
            'translations' => [
                'hotspot' => [
                    'class' => 'yii\i18n\PhpMessageSource',
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

        // Registers javascript after `AssetActiveForm` is rendered
        Yii::$container->set(Asset::instance()->getActiveForm(), [
            'on afterRun' => function (WidgetEvent $event) {
                /** @var AssetActiveForm $form */
                $form = $event->sender;
                /** @var Module $module */
                $module = Yii::$app->getModule('admin')->getModule('hotspot');

                if ($form->model->isSectionAsset() ? $module->enableSectionAssetHotspots : $module->enableEntryAssetHotspots) {
                    if ($form->model->file->hasPreview()) {
                        if (!$form->model->isRelationPopulated('hotspots')) {
                            if ($form->model->getAttribute('hotspot_count')) {
                                $hotspots = Hotspot::find()
                                    ->where(['asset_id' => $form->model->id])
                                    ->orderBy(['position' => SORT_ASC])
                                    ->all();
                            }

                            $form->model->populateRelation('hotspots', $hotspots ?? []);
                        }

                        $hotspots = $form->model->getRelatedRecords()['hotspots'] ?? [];

                        $options = array_filter([
                            'formName' => Hotspot::instance()->formName(),
                            'url' => Url::toRoute(['/admin/hotspot/create', 'id' => $form->model->id]),
                            'message' => !$hotspots ? Yii::t('hotspot', 'Double click on the image to create a hotspot.') : null,
                            'hotspots' => $hotspots,
                        ]);

                        AdminAsset::register($view = $form->getView());
                        $view->registerJs('Skeleton.registerHotspots(' . Json::htmlEncode($options) . ')');
                    }
                }
            }
        ]);

        // Makes sure hotspots (and their assets) are deleted on asset delete
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