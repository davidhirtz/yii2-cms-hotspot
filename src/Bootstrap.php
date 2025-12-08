<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot;

use Hirtz\Cms\hotspot\models\events\AssetAfterDuplicateEventHandler;
use Hirtz\Cms\hotspot\models\events\AssetBeforeDeleteEventHandler;
use Hirtz\Cms\hotspot\models\events\AssetBeforeDuplicateEventHandler;
use Hirtz\Cms\hotspot\models\events\FileBeforeDeleteEventHandler;
use Hirtz\Cms\hotspot\models\HotspotAsset;
use Hirtz\Cms\hotspot\modules\admin\Module;
use Hirtz\Cms\models\Asset;
use Hirtz\Cms\models\builders\EntrySiteRelationsBuilder;
use Hirtz\Cms\modules\admin\widgets\grids\columns\AssetThumbnailColumn;
use Hirtz\Cms\widgets\Canvas;
use Hirtz\Media\models\File;
use Hirtz\Media\modules\admin\widgets\forms\fields\AssetPreview;
use Hirtz\Skeleton\models\actions\DuplicateActiveRecord;
use Hirtz\Skeleton\models\events\DuplicateActiveRecordEvent;
use Hirtz\Skeleton\web\Application;
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

        $app->setMigrationNamespace('Hirtz\Cms\hotspot\migrations');
    }
}
