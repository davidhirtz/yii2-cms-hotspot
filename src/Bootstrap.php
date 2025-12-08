<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot;

use Hirtz\Cms\hotspot\models\events\AssetAfterDuplicateEventHandler;
use Hirtz\Cms\hotspot\models\events\AssetBeforeDeleteEventHandler;
use Hirtz\Cms\hotspot\models\events\AssetBeforeDuplicateEventHandler;
use Hirtz\Cms\hotspot\models\events\FileBeforeDeleteEventHandler;
use Hirtz\Cms\hotspot\models\HotspotAsset;
use Hirtz\Cms\hotspot\Modules\Admin\Module;
use Hirtz\Cms\Models\Asset;
use Hirtz\Cms\Models\builders\EntrySiteRelationsBuilder;
use Hirtz\Cms\Modules\Admin\Widgets\Forms\Columns\AssetThumbnailColumn;
use Hirtz\Cms\widgets\Canvas;
use Hirtz\Media\models\File;
use Hirtz\Media\Modules\Admin\Widgets\Forms\Fields\AssetPreview;
use Hirtz\Skeleton\Models\Actions\DuplicateActiveRecord;
use Hirtz\Skeleton\Models\Events\DuplicateActiveRecordEvent;
use Hirtz\Skeleton\Web\Application;
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
            'basePath' => '@hotspot/../messages',
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
            AssetPreview::class => Modules\Admin\Widgets\Forms\Fields\AssetPreview::class,
            AssetThumbnailColumn::class => Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn::class,
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

        $app->setMigrationNamespace('Hirtz\Cms\hotspot\Migrations');
    }
}
