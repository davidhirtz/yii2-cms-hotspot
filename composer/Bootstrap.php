<?php

namespace davidhirtz\yii2\hotspot\composer;

use davidhirtz\yii2\hotspot\assets\AdminAsset;
use davidhirtz\yii2\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\modules\admin\widgets\forms\AssetActiveForm;
use davidhirtz\yii2\skeleton\web\Application;
use yii\base\BootstrapInterface;
use Yii;
use yii\base\WidgetEvent;
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
            'admin' => [
                'modules' => [
                    'hotspot' => [
                        'class' => 'davidhirtz\yii2\hotspot\modules\admin\Module',
                    ],
                ],
            ],
            'media' => [
                'class' => 'davidhirtz\yii2\media\Module',
                'assets' => [
                    'davidhirtz\yii2\hotspot\models\HotspotAsset',
                ],
            ],
        ]);

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

                    $buttons = [];

                    AdminAsset::register($view = $form->getView());
                    $view->registerJs('Skeleton.registerHotspots("' . Url::toRoute(['/admin/hotspot/create', 'id' => $form->model->id]) . '")');
                }
            }
        ]);

        $app->setMigrationNamespace('davidhirtz\yii2\hotspot\migrations');
    }
}