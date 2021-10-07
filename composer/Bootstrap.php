<?php

namespace davidhirtz\yii2\hotspot\composer;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\models\base\ModelCloneEvent;
use davidhirtz\yii2\hotspot\assets\AdminAsset;
use davidhirtz\yii2\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\modules\admin\widgets\forms\AssetActiveForm;
use davidhirtz\yii2\skeleton\modules\admin\Module;
use davidhirtz\yii2\skeleton\web\Application;
use yii\base\BootstrapInterface;
use Yii;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\base\WidgetEvent;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Class Bootstrap
 * @package davidhirtz\yii2\hotspot\bootstrap
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
            'media' => [
                'assets' => [
                    'davidhirtz\yii2\hotspot\models\HotspotAsset',
                ],
            ],
        ]);

        // Registers javascript after `AssetActiveForm` is rendered
        Yii::$container->set(AssetActiveForm::class, [
            'on afterRun' => function (WidgetEvent $event) {
                /** @var AssetActiveForm $form */
                $form = $event->sender;

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
        ]);

        // Set default routes.
        Yii::$container->set(Module::class, [
            'on afterInit' => function (Event $event) {
                /** @var Module $module */
                $module = $event->sender;

                $controllerMap = [
                    'hotspot' => [
                        'class' => 'davidhirtz\yii2\hotspot\modules\admin\controllers\HotspotController',
                        'viewPath' => '@hotspot/modules/admin/views/hotspot',
                    ],
                    'hotspot-asset' => [
                        'class' => 'davidhirtz\yii2\hotspot\modules\admin\controllers\HotspotAssetController',
                        'viewPath' => '@hotspot/modules/admin/views/hotspot-asset',
                    ],
                ];

                $module->controllerMap = array_merge($controllerMap, $module->controllerMap);
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
                    $hotspot->clone(['asset_id' => $clone->id]);
                }
            }
        });

        $app->setMigrationNamespace('davidhirtz\yii2\hotspot\migrations');
    }
}