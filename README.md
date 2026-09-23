# yii2-cms-hotspot

Image hotspots for [yii2-cms](https://github.com/davidhirtz/yii2-cms): markers placed on an entry, section or block
asset, each with a name, content, a link, a type and optionally assets of its own. The admin adds an editor to the
asset preview, the frontend widget renders them over the image, and the cms site preload loads them with the entry.
Requires `davidhirtz/yii2-cms` 3.0, which brings `yii2-media`, `yii2-tenant` and `yii2-skeleton`.

## Installation

```bash
composer require davidhirtz/yii2-cms-hotspot
./yii migrate
./yii search/rebuild
```

The bundle bootstraps itself through `extra.bootstrap` (`Hirtz\Cms\Hotspot\Bootstrap`): it registers the `hotspot`
module and the `admin/hotspot` submodule, adds `Models\HotspotAsset` to `modules.media.assets`, registers `Models\Hotspot`
and `Models\HotspotAsset` with the `search` component, the `hotspot` message category and the `@hotspot` alias, and
subscribes to the delete and duplicate events of `EntryAsset`, `SectionAsset` and `BlockAsset`. The migration adds the
`hotspot` table and `asset.hotspot_count`.

## Configuration

Module properties, under `modules.hotspot`:

| Property                     | Default | Meaning                                                                |
|------------------------------|---------|------------------------------------------------------------------------|
| `enableEntryAssetHotspots`   | `false` | Entry assets can carry hotspots                                        |
| `enableSectionAssetHotspots` | `true`  | Section assets can carry hotspots; block assets follow this flag       |
| `enableHotspotAssets`        | `true`  | A hotspot can carry assets of its own (`Models\HotspotAsset`)          |

`Module::allowsHotspots($asset)` is the single reader of these flags, for the admin editor, the frontend widget and the
site preload alike. It also honours the marker below.

### Turning hotspots off per asset type

A media asset type hides its hotspots by naming `Module::FIELD_HOTSPOTS` (`'hotspots'`) in its `hiddenFields()`:

```php
use Hirtz\Cms\Hotspot\Module;
use Hirtz\Media\Models\Types\AssetType;

AssetType::make(2)
    ->name('Plain image')
    ->hiddenFields(Module::FIELD_HOTSPOTS);
```

### Container definitions

`Models\Hotspot` is configured like every skeleton model. Its `name`, `content` and `link` are custom attributes; the
ones to translate are `translatableAttributes` (never `i18nAttributes`, which names columns). Types are
`Models\Types\HotspotType` objects, carrying the media `sizes()`, `transformations()` and `allowAssets()`:

```php
'container' => [
    'definitions' => [
        \Hirtz\Cms\Hotspot\Models\Hotspot::class => [
            'translatableAttributes' => ['name', 'content'],
            'types' => fn (): array => [
                \Hirtz\Cms\Hotspot\Models\Types\HotspotType::make(1)
                    ->name('Marker'),
                \Hirtz\Cms\Hotspot\Models\Types\HotspotType::make(2)
                    ->name('Gallery')
                    ->transformations('w_400')
                    ->customAttributes([
                        \Hirtz\Skeleton\Models\CustomAttributes\TextCustomAttribute::make('caption'),
                    ]),
            ],
        ],
    ],
],
```

`Models\HotspotAsset` is a `Hirtz\Media\Models\Asset` subclass and is configured as one; it offers `name`, `content`,
`alt_text` and `link` only.

### Widgets replaced through the container

The bootstrap re-points three classes to hotspot-aware subclasses unless the container already defines them:
the cms `Widgets\Artwork`, the media `Modules\Admin\Widgets\Forms\Fields\AssetPreviewField` (the editor) and the media
`Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn` (the hotspot count badge). A project overriding one of them
extends the hotspot subclass.

## The `Artwork` widget

`Widgets\Artwork` renders the asset as the cms widget does and, where `allowsHotspots()` answers `true` and the asset
has hotspots, wraps media and hotspots in a `<div class="relative">`. The hotspots come from the view named by
`hotspotViewFile()` (default `widgets/_hotspots`, resolved relative to the page's view like the cms `widgets/_embed`),
which receives `$hotspots`, a list of `Models\Hotspot`:

```php
<?php foreach ($hotspots as $hotspot): ?>
    <a id="<?= $hotspot->getHtmlId() ?>" href="<?= $hotspot->link ?>"
       style="left: <?= $hotspot->x ?>%; top: <?= $hotspot->y ?>%"><?= $hotspot->name ?></a>
<?php endforeach; ?>
```

`$hotspot->getVisibleAssets()` answers the hotspot's own assets, `hotspotViewFile(false)` renders none, and
`hotspotWrapper(Closure)` receives the wrapping `Div` for a class or an attribute of its own. The `relative` class is
not shipped; the project defines it.

## Admin

Hotspots are placed by double-clicking the asset preview on the asset's page (`resources/assets/dist/js/hotspot.js`,
registered by `Assets\HotspotAdminAssetBundle`) and dragged into place; each hotspot has a page of its own under
`/admin/hotspot/hotspot/update` with its assets under `/admin/hotspot/hotspot-asset/index`. Editing needs
`Entry::AUTH_ENTRY` or `Block::AUTH_BLOCK` plus the permission of the asset the hotspot sits on. Duplicating or
deleting an asset duplicates or deletes its hotspots.
