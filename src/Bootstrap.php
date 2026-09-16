<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot;

use Hirtz\Cms\Hotspot\Events\HotspotEntrySiteRelationsEventHandler;
use Hirtz\Cms\Hotspot\Models\Events\AssetAfterDuplicateEventHandler;
use Hirtz\Cms\Hotspot\Models\Events\AssetBeforeDeleteEventHandler;
use Hirtz\Cms\Hotspot\Models\Events\AssetBeforeDuplicateEventHandler;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Models\EntryAsset;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Cms\Models\Actions\PreloadEntrySiteRelations;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\CmsNavItem;
use Hirtz\Media\Models\Asset;
use Hirtz\Media\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn;
use Hirtz\Cms\Widgets\Artwork;
use Hirtz\Media\Modules\Admin\Widgets\Forms\Fields\AssetPreviewField;
use Hirtz\Skeleton\Helpers\EventHelper;
use Hirtz\Skeleton\Models\Actions\DuplicateActiveRecord;
use Hirtz\Skeleton\Models\Events\DuplicateActiveRecordEvent;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Web\Application;
use Hirtz\Skeleton\Widgets\Widget;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\db\BaseActiveRecord;
use yii\i18n\PhpMessageSource;

class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application<User> $app
     */
    public function bootstrap($app): void
    {
        Yii::setAlias('@hotspot', __DIR__);

        $app->getI18n()->translations['hotspot'] ??= [
            'class' => PhpMessageSource::class,
            'basePath' => '@hotspot/../messages',
                    'forceTranslation' => true,
];

        $app->extendComponent('search', [
            'models' => [
                Hotspot::class,
                HotspotAsset::class,
            ],
        ]);

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'hotspot' => [
                        'class' => Modules\Admin\Module::class,
                    ],
                ],
            ],
            'hotspot' => [
                'class' => Module::class,
            ],
            'media' => [
                'assets' => [HotspotAsset::class],
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

        // Both kinds of cms asset can carry hotspots.
        foreach ([EntryAsset::class, SectionAsset::class] as $assetClass) {
            EventHelper::on(
                $assetClass,
                BaseActiveRecord::EVENT_BEFORE_DELETE,
                fn (Asset $asset, ModelEvent $event) => Yii::createObject(AssetBeforeDeleteEventHandler::class, [
                    $event,
                    $asset,
                ]),
                ModelEvent::class
            );

            EventHelper::on(
                $assetClass,
                DuplicateActiveRecord::EVENT_BEFORE_DUPLICATE,
                fn (Asset $asset, DuplicateActiveRecordEvent $event) => Yii::createObject(
                    AssetBeforeDuplicateEventHandler::class,
                    [$event, $asset, $event->duplicate]
                ),
                DuplicateActiveRecordEvent::class
            );

            EventHelper::on(
                $assetClass,
                DuplicateActiveRecord::EVENT_AFTER_DUPLICATE,
                fn (Asset $asset, DuplicateActiveRecordEvent $event) => Yii::createObject(
                    AssetAfterDuplicateEventHandler::class,
                    [$event, $asset, $event->duplicate]
                ),
                DuplicateActiveRecordEvent::class
            );
        }

        Event::on(
            PreloadEntrySiteRelations::class,
            PreloadEntrySiteRelations::EVENT_AFTER_LOAD_ASSETS,
            new HotspotEntrySiteRelationsEventHandler(),
        );

        $this->addCmsNavItemRoutes();

        $app->setMigrationNamespace('Hirtz\Cms\Hotspot\Migrations');
    }

    /**
     * A hotspot is always reached from a cms asset, so its pages belong to the entries item — which the bundle adds
     * itself rather than leaving the cms to name a bundle that may not be installed.
     */
    protected function addCmsNavItemRoutes(): void
    {
        EventHelper::on(
            CmsNavItem::class,
            Widget::EVENT_CONFIGURE,
            static function (CmsNavItem $item): void {
                $item->routes([
                    'admin/hotspot/hotspot',
                    'admin/hotspot/hotspot-asset',
                ]);
            }
        );
    }
}
