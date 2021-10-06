<?php

namespace davidhirtz\yii2\annotation\composer;

use davidhirtz\yii2\annotation\assets\AdminAsset;
use davidhirtz\yii2\cms\modules\admin\widgets\forms\AssetActiveForm;
use davidhirtz\yii2\skeleton\web\Application;
use yii\base\BootstrapInterface;
use Yii;
use yii\base\WidgetEvent;
use yii\helpers\Url;

/**
 * Class Bootstrap
 * @package davidhirtz\yii2\annotation\bootstrap
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@annotation', dirname(__DIR__));

        $app->extendComponent('i18n', [
            'translations' => [
                'annotation' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@annotation/messages',
                ],
            ],
        ]);

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'annotation' => [
                        'class' => 'davidhirtz\yii2\annotation\modules\admin\Module',
                    ],
                ],
            ],
            'media' => [
                'class' => 'davidhirtz\yii2\media\Module',
                'assets' => [
                    'davidhirtz\yii2\annotation\models\AnnotationAsset',
                ],
            ],
        ]);

        Yii::$container->set(AssetActiveForm::class, [
            'on afterRun' => function (WidgetEvent $event) {
                /** @var AssetActiveForm $form */
                $form = $event->sender;

                if ($form->model->file->hasPreview()) {
                    AdminAsset::register($view = $form->getView());
                    $view->registerJs('Skeleton.registerAnnotations("'. Url::toRoute(['/admin/annotation/create', 'id' => $form->model->id]) .'")');
                }
            }
        ]);

        $app->setMigrationNamespace('davidhirtz\yii2\annotation\migrations');
    }
}