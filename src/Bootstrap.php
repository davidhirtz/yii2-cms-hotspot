<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot;

use Hirtz\Cms\Hotspot\Events\HotspotEntrySiteRelationsBuilderEventHandler;
use Hirtz\Cms\Hotspot\Models\Events\AssetAfterDuplicateEventHandler;
use Hirtz\Cms\Hotspot\Models\Events\AssetBeforeDeleteEventHandler;
use Hirtz\Cms\Hotspot\Models\Events\AssetBeforeDuplicateEventHandler;
use Hirtz\Cms\Hotspot\Models\Events\FileBeforeDeleteEventHandler;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Module;
use Hirtz\Cms\Models\Asset;
use Hirtz\Cms\Models\Builders\EntrySiteRelationsBuilder;
use Hirtz\Cms\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn;
use Hirtz\Cms\Widgets\Artwork;
use Hirtz\Media\Models\File;
use Hirtz\Media\Modules\Admin\Widgets\Forms\Fields\AssetPreviewField;
use Hirtz\Skeleton\Models\Actions\DuplicateActiveRecord;
use Hirtz\Skeleton\Models\Events\DuplicateActiveRecordEvent;
use Hirtz\Skeleton\Web\Application;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
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
            AssetPreviewField::class => Modules\Admin\Widgets\Forms\Fields\AssetPreviewField::class,
            AssetThumbnailColumn::class => Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn::class,
            Artwork::class => Widgets\Artwork::class,
        ];

        foreach ($definitions as $class => $definition) {
            if (!Yii::$container->has($class)) {
                Yii::$container->set($class, $definition);
            }
        }

        Event::on(
            Asset::class,
            Asset::EVENT_BEFORE_DELETE,
            fn (ModelEvent $event) => Yii::createObject(AssetBeforeDeleteEventHandler::class, [
                $event,
                $event->sender,
            ])
        );

        Event::on(
            Asset::class,
            DuplicateActiveRecord::EVENT_BEFORE_DUPLICATE,
            fn (DuplicateActiveRecordEvent $event) => Yii::createObject(AssetBeforeDuplicateEventHandler::class, [
                $event,
                $event->sender,
                $event->duplicate,
            ])
        );

        Event::on(
            Asset::class,
            DuplicateActiveRecord::EVENT_AFTER_DUPLICATE,
            fn (DuplicateActiveRecordEvent $event) => Yii::createObject(AssetAfterDuplicateEventHandler::class, [
                $event,
                $event->sender,
                $event->duplicate,
            ])
        );

        Event::on(
            File::class,
            File::EVENT_BEFORE_DELETE,
            fn (ModelEvent $event) => Yii::createObject(FileBeforeDeleteEventHandler::class, [
                $event,
                $event->sender,
            ])
        );

        Event::on(
            EntrySiteRelationsBuilder::class,
            EntrySiteRelationsBuilder::EVENT_AFTER_LOAD_ASSETS,
            new HotspotEntrySiteRelationsBuilderEventHandler(),
        );

        $app->setMigrationNamespace('Hirtz\Cms\Hotspot\Migrations');
    }
}
