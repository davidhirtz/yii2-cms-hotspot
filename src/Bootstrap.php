<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot;

use davidhirtz\yii2\cms\hotspot\models\events\AssetAfterDuplicateEventHandler;
use davidhirtz\yii2\cms\hotspot\models\events\AssetBeforeDeleteEventHandler;
use davidhirtz\yii2\cms\hotspot\models\events\AssetBeforeDuplicateEventHandler;
use davidhirtz\yii2\cms\hotspot\models\events\FileBeforeDeleteEventHandler;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\hotspot\modules\admin\Module;
use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\models\builders\EntrySiteRelationsBuilder;
use davidhirtz\yii2\cms\modules\admin\widgets\grids\columns\AssetThumbnailColumn;
use davidhirtz\yii2\cms\widgets\Canvas;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\media\modules\admin\widgets\forms\fields\AssetPreview;
use davidhirtz\yii2\skeleton\models\actions\DuplicateActiveRecord;
use davidhirtz\yii2\skeleton\models\events\DuplicateActiveRecordEvent;
use davidhirtz\yii2\skeleton\web\Application;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\ModelEvent;
use yii\i18n\PhpMessageSource;

class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        Yii::setAlias('@hotspot', __DIR__);

        $app->getI18n()->translations['hotspot'] ??= [
            'class' => PhpMessageSource::class,
            'basePath' => '@hotspot/messages',
        ];

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'hotspot' => [
                        'class' => Module::class,
                    ],
                ],
            ],
            'media' => [
                'fileRelations' => [HotspotAsset::class],
            ],
        ]);

        $definitions = [
            AssetPreview::class => modules\admin\widgets\forms\fields\AssetPreview::class,
            AssetThumbnailColumn::class => modules\admin\widgets\grids\columns\AssetThumbnailColumn::class,
            Canvas::class => widgets\Canvas::class,
            EntrySiteRelationsBuilder::class => models\builders\EntrySiteRelationsBuilder::class,
        ];

        foreach ($definitions as $class => $definition) {
            if (!Yii::$container->has($class)) {
                Yii::$container->set($class, $definition);
            }
        }

        ModelEvent::on(
            Asset::class,
            Asset::EVENT_BEFORE_DELETE,
            fn (ModelEvent $event) => Yii::createObject(AssetBeforeDeleteEventHandler::class, [
                $event,
                $event->sender,
            ])
        );

        ModelEvent::on(
            Asset::class,
            DuplicateActiveRecord::EVENT_BEFORE_DUPLICATE,
            fn (DuplicateActiveRecordEvent $event) => Yii::createObject(AssetBeforeDuplicateEventHandler::class, [
                $event,
                $event->sender,
                $event->duplicate,
            ])
        );

        ModelEvent::on(
            Asset::class,
            DuplicateActiveRecord::EVENT_AFTER_DUPLICATE,
            fn (DuplicateActiveRecordEvent $event) => Yii::createObject(AssetAfterDuplicateEventHandler::class, [
                $event,
                $event->sender,
                $event->duplicate,
            ])
        );

        ModelEvent::on(
            File::class,
            File::EVENT_BEFORE_DELETE,
            fn (ModelEvent $event) => Yii::createObject(FileBeforeDeleteEventHandler::class, [
                $event,
                $event->sender,
            ])
        );

        $app->setMigrationNamespace('davidhirtz\yii2\cms\hotspot\migrations');
    }
}
