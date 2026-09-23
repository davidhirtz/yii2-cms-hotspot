# Upgrading to 3.0

## Requirements

- PHP `^8.3`
- `davidhirtz/yii2-cms` `^3.0`, which brings `yii2-media`, `yii2-tenant` and `yii2-skeleton` at 3.0. Upgrade those
  first; their guides cover the namespace rename, the type objects, the custom attributes and the `asset` table.
- After `composer update`: `./yii migrate`, then `./yii search/rebuild` once.

## Renames

### Namespaces and classes

| v2                                                                    | v3                                                                |
|-----------------------------------------------------------------------|-------------------------------------------------------------------|
| `davidhirtz\yii2\cms\hotspot\`                                        | `Hirtz\Cms\Hotspot\`                                              |
| `models\Hotspot`, `models\HotspotAsset`                               | `Models\Hotspot`, `Models\HotspotAsset`                           |
| `models\queries\HotspotQuery`                                         | `Models\Queries\HotspotQuery`                                     |
| `models\queries\HotspotAssetQuery`                                    | removed, `Hirtz\Media\Models\Queries\AssetQuery`                  |
| `models\actions\DuplicateHotspot`                                     | `Models\Actions\DuplicateHotspot`                                 |
| `models\actions\ReorderHotspotAssets`                                 | removed, the media `AssetControllerTrait::reorderAssets()`        |
| `models\builders\EntrySiteRelationsBuilder`                           | `Events\HotspotEntrySiteRelationsEventHandler`                    |
| `models\events\Asset*EventHandler`                                    | `Models\Events\Asset*EventHandler`                                |
| `models\events\FileBeforeDeleteEventHandler`                          | removed                                                           |
| `modules\admin\Module`                                                | `Modules\Admin\Module` (routing only) plus the new base `Module`  |
| `modules\admin\controllers\HotspotController`                         | `Modules\Admin\Controllers\HotspotController`                     |
| `modules\admin\controllers\HotspotAssetController`                    | `Modules\Admin\Controllers\HotspotAssetController`                |
| `modules\admin\widgets\forms\HotspotActiveForm`                       | `Modules\Admin\Widgets\Forms\HotspotActiveForm`                   |
| `modules\admin\widgets\forms\HotspotAssetActiveForm`                  | removed, the media `AssetActiveForm`                              |
| `modules\admin\widgets\forms\fields\AssetPreview`                     | `Modules\Admin\Widgets\Forms\Fields\AssetPreviewField`            |
| `modules\admin\widgets\grids\columns\AssetThumbnailColumn`            | `Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn`        |
| `modules\admin\widgets\grids\columns\HotspotAssetThumbnailColumn`     | removed                                                           |
| `modules\admin\widgets\grids\HotspotAssetGridView`, `…ParentGridView` | removed, the media `AssetGridView`                                |
| `modules\admin\widgets\navs\HotspotSubmenu`                           | `Modules\Admin\Widgets\Navs\HotspotSubmenu`                       |
| `modules\admin\widgets\panels\HotspotAssetFilePanel`, `HotspotHelpPanel` | removed                                                        |
| `widgets\Canvas`                                                      | `Widgets\Artwork`                                                 |
| `assets\AdminAsset`                                                   | `Assets\HotspotAdminAssetBundle`                                  |
| `migrations\M211006182918Hotspot`                                     | `Migrations\M260101000500CmsHotspotBaseline` (fresh installs)     |

New: `Module` (the flags and `allowsHotspots()`), `Modules\ModuleTrait`, `Models\Types\HotspotType`,
`Modules\Admin\Widgets\Navs\HotspotHeader`, `Modules\Admin\Widgets\Navs\HotspotActionDropdown`.

### Methods and properties

| v2                                                  | v3                                                                |
|-----------------------------------------------------|-------------------------------------------------------------------|
| `Hotspot::hasAssetsEnabled()`                       | `Hotspot::allowsAssets()`                                         |
| `Hotspot::getDisplayName()`, `getTrailModelName()`  | `Hotspot::getAdminName()`                                         |
| `Hotspot::getTrailModelType()`                      | `Hotspot::getAdminType()`                                         |
| `Hotspot::getTrailModelAdminRoute()`                | `Hotspot::getAdminRoute()`                                        |
| `Hotspot::$contentType`, `$htmlValidator`           | removed, `content` is an `HtmlCustomAttribute`                    |
| `Hotspot::$i18nAttributes` (for name/content/link)  | `Hotspot::$translatableAttributes`                                |
| `Hotspot::getModule()` (the cms module)             | `Hotspot::getModule()` (the `hotspot` module)                     |
| `HotspotAsset::$hotspot`, `getHotspot()`            | `HotspotAsset::$model`, `getModel()`                              |
| `HotspotAsset::getParent()`                         | `HotspotAsset::$model`                                            |
| `HotspotAsset::getTrailModelType()`                 | `HotspotAsset::getAdminType()`                                    |
| `HotspotAsset::getFilePanelClass()`                 | removed                                                           |
| `HotspotAsset::getFileCountAttributeNames()`        | removed, `file.asset_count`                                       |
| `HotspotAsset::updateFileRelatedCount()`            | removed, the media `Asset` recounts                               |
| `HotspotAsset::FIELD_HOTSPOTS`, `'#hotspots'`       | `Module::FIELD_HOTSPOTS` (`'hotspots'`)                           |
| `Canvas::$template` with `{hotspots}`               | removed, hotspots are rendered inside `Artwork::renderMedia()`    |
| `Canvas::$hotspotViewFile`                          | `Artwork::hotspotViewFile()`                                      |
| `HotspotSubmenu::$hotspot`                          | `HotspotSubmenu::model()`, `HotspotHeader::model()`               |
| `HotspotAssetController::actionDuplicate()`         | `HotspotAssetController::actionRemove()`                          |

### Configuration keys

| v2                                                   | v3                                              |
|------------------------------------------------------|-------------------------------------------------|
| `modules.admin.modules.hotspot.enableEntryAssetHotspots`   | `modules.hotspot.enableEntryAssetHotspots`   |
| `modules.admin.modules.hotspot.enableSectionAssetHotspots` | `modules.hotspot.enableSectionAssetHotspots` |
| `modules.admin.modules.hotspot.enableHotspotAssets`        | `modules.hotspot.enableHotspotAssets`        |
| `modules.media.fileRelations` (HotspotAsset entry)         | `modules.media.assets`                       |

### Tables and columns

| v2                                          | v3                                                          |
|---------------------------------------------|-------------------------------------------------------------|
| `hotspot.name`, `content`, `link` (+ `_xx`) | `hotspot.custom_attributes` (JSON, translations inside)     |
| `hotspot_asset` table                       | rows in `asset` with `model_class = HotspotAsset`, kept as a read-only reference |
| `hotspot_asset.hotspot_id`                  | `asset.model_id`                                            |
| `hotspot_asset.name`, `content`, `alt_text`, `link` | `asset.custom_attributes`                           |
| `file.hotspot_asset_count` (+ `_xx`)        | folded into `file.asset_count`                              |
| `cms_asset.hotspot_count`                   | `asset.hotspot_count`                                       |

### Routes

| v2                              | v3                                        |
|---------------------------------|-------------------------------------------|
| `/admin/hotspot/update`         | `/admin/hotspot/hotspot/update`           |
| `/admin/hotspot/create`         | `/admin/hotspot/hotspot/create`           |
| `/admin/hotspot/delete`, `duplicate` | `/admin/hotspot/hotspot/delete`, `duplicate` |
| `/admin/hotspot-asset/index`    | `/admin/hotspot/hotspot-asset/index` (the hotspot's assets) |
| `/admin/hotspot-asset/*`        | `/admin/hotspot/hotspot-asset/*`; `create` is the file picker, `duplicate` is `remove` |

### Message keys

The `hotspot` category is keyed by constants now, every key present in `de`, `en-US`, `fr` and `pt`:

| v2 (English text as key)                          | v3                              |
|---------------------------------------------------|---------------------------------|
| `Hotspot`                                         | `COMMON_HOTSPOT`                |
| `Hotspot Asset`                                   | `COMMON_HOTSPOT_ASSET`          |
| `Double click on the image to create a hotspot.`  | `ASSET_PREVIEW_DOUBLE_CLICK`    |
| `Delete Hotspot`                                  | `HOTSPOT_BUTTON_DELETE`, `HOTSPOT_CONFIRM_DELETE` |
| `The hotspot was updated.` / `deleted.` / `duplicated.` | `HOTSPOT_SUCCESS_UPDATED` / `_DELETED` / `_DUPLICATED`; new `HOTSPOT_SUCCESS_CREATED` |
| `Horizontal position`, `Vertical position`        | `HOTSPOT_X_LABEL`, `HOTSPOT_Y_LABEL` |
| `Edit Hotspot`, `Edit Hotspot Asset`, `Hotspots`, `The hotspot asset was …`, `Hotspot asset order changed` | removed; the media `MODEL_ASSET_*` keys |

The hotspot's attribute labels are the cms keys `HOTSPOT_NAME_LABEL`, `HOTSPOT_CONTENT_LABEL`, `HOTSPOT_LINK_LABEL`
and `HOTSPOT_ASSET_ID_LABEL`; `asset_count` reads the media `MODEL_ASSET_COUNT_LABEL`. `HOTSPOT_BUTTON_DUPLICATE` is new.

### CSS

`.hotspot-btn` and `.hotspot-icon` are unchanged. `.hotspot-canvas` is gone: the admin editor positions the buttons
inside the media preview's own wrapper. The frontend markup is the project's `_hotspots` view, so nothing changes there.

## Configuration

The three flags move to a module of their own, `modules.hotspot`, because the frontend reads them as much as the admin:

```php
// v2
'modules' => [
    'admin' => [
        'modules' => [
            'hotspot' => [
                'enableEntryAssetHotspots' => true,
            ],
        ],
    ],
],

// v3
'modules' => [
    'hotspot' => [
        'enableEntryAssetHotspots' => true,
    ],
],
```

Types, translatable attributes and custom attributes are container definitions on `Models\Hotspot`, as on every
skeleton model. A translated `name`, `content` or `link` moves from `i18nAttributes` to `translatableAttributes`:

```php
'container' => [
    'definitions' => [
        \Hirtz\Cms\Hotspot\Models\Hotspot::class => [
            'translatableAttributes' => ['name', 'content'],
            'types' => fn (): array => [
                \Hirtz\Cms\Hotspot\Models\Types\HotspotType::make(1)
                    ->name('Marker')
                    ->allowAssets(false),
            ],
        ],
    ],
],
```

A project that overrode the cms `Canvas`, the cms `AssetThumbnailColumn` or the media `AssetPreview` through the
container re-keys the definition: the bundle sets `Hirtz\Cms\Widgets\Artwork`,
`Hirtz\Media\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn` and
`Hirtz\Media\Modules\Admin\Widgets\Forms\Fields\AssetPreviewField` only when nothing else has, so a project
definition still wins as long as it extends the hotspot subclass.

## Code changes

### `Widgets\Canvas` is `Widgets\Artwork`

`Widgets\Artwork` extends the cms `Widgets\Artwork` and renders the hotspots inside `renderMedia()`: the media and the
hotspot view are wrapped in a `Div` with the class `relative` (the class the project defines, see the cms guide). The
`{hotspots}` template token no longer exists, so a project template naming it drops the token. The view file and the
wrapper are setters:

```php
echo Artwork::make()
    ->asset($asset)
    ->hotspotViewFile('widgets/_hotspots')
    ->hotspotWrapper(fn (Div $wrapper) => $wrapper->addClass('overflow-hidden'));
```

`hotspotViewFile(false)` renders no hotspots. The default `widgets/_hotspots` is resolved relative to the page's view
like the cms `widgets/_embed`, so it lives beside the project's site views; the view receives `$hotspots`, a list of
`Models\Hotspot` with `getVisibleAssets()` for the hotspot's own assets. The hotspots are only rendered where
`Module::allowsHotspots($asset)` answers `true`, which replaces the v2 `isAttributeVisible('#hotspots')` check.

### The hidden-field marker is `Module::FIELD_HOTSPOTS`

An asset type that hides its hotspots names `Hirtz\Cms\Hotspot\Module::FIELD_HOTSPOTS` (`'hotspots'`) in its
`hiddenFields()`. `HotspotAsset::FIELD_HOTSPOTS` and the literal `'#hotspots'` are gone, and the marker is checked on
the entry, section or block asset, in the admin and the frontend alike through `Module::allowsHotspots()`.

```php
// on the project's media AssetType subclass
$this->hiddenFields(\Hirtz\Cms\Hotspot\Module::FIELD_HOTSPOTS);
```

### `name`, `content` and `link` are custom attributes

They are read and assigned like before (`$hotspot->name`, `$hotspot->getI18nAttribute('content')`) but hold no column
of their own, so `Hotspot::find()->where(['name' => …])`, an `orderBy('name')` or a grid sort on them throws. A project
that set `Hotspot::$contentType` or `$htmlValidator` declares its own definitions instead by overriding
`getCustomAttributes()`; the shipped `content` is an `HtmlCustomAttribute`, `link` a `UrlCustomAttribute` and is
validated as a URL now. A hotspot type contributes further definitions through `HotspotType::customAttributes()`.

### Types are `Models\Types\HotspotType` objects

`Hotspot::getTypes()` answers `HotspotType` instances (fluent `name()`, `icon()`, `hiddenFields()`, `customAttributes()`,
`allowAssets()`, `sizes()`, `transformations()`), declared through the container as shown above or by overriding the
instance method. The v2 array form is refused. `Hotspot::allowsAssets()` (was `hasAssetsEnabled()`) combines
`enableHotspotAssets` with the type's `allowAssets()`.

### `HotspotAsset` is a media `Asset`

It extends `Hirtz\Media\Models\Asset` and lives in the `asset` table with `model_class` set to its class name. The
hotspot is `$asset->model` (was `$hotspot`), `hotspot_id` is `model_id`, `find()` is scoped to the class and
`where()` on it replaces that scope (use `andWhere()`). The text attributes are the media asset's custom attributes,
narrowed to `name`, `content`, `alt_text` and `link`; `embed_url`, `loading` and `fetchpriority` are not offered.
Reordering, status, removal and deletion go through the media `AssetControllerTrait`, so a project's own hotspot asset
controller or grid subclasses the media ones.

### Admin widgets and views

`HotspotHeader` is a skeleton `ModelHeader` that titles the page with the record the asset hangs on and names the
hotspot as the subtitle; `HotspotSubmenu` is a skeleton `Submenu` with the hotspot's own tab and its assets tab. Both
take the hotspot through `model()`; `HotspotSubmenu::$hotspot` and the breadcrumb override are gone. A project view for
`hotspot/update` renders `HotspotHeader`, `HotspotActionDropdown`, `HotspotSubmenu` and a `FormContainer` around
`HotspotActiveForm`, as `resources/views/admin/hotspot/update.php` does. `HotspotActiveForm` declares its fields in
`getDefaultRows()`; a subclass that assigned rows in `init()` or `configure()` moves to that hook.

### Access control and routes

`HotspotController` and `HotspotAssetController` require `Hirtz\Cms\Models\Entry::AUTH_ENTRY` or
`Hirtz\Cms\Models\Block::AUTH_BLOCK` and then check the permission of the asset the hotspot sits on. The v2
`entryAssetUpdate` and `sectionAssetUpdate` items no longer exist. Every admin URL gained the module segment
(`/admin/hotspot/hotspot/update`); a project linking to a hotspot uses `$hotspot->getAdminRoute()`.

### The create endpoint answers an envelope

`HotspotController::actionCreate()` answers `{"hotspot": {...}, "flashes": "<html>"}` instead of the bare hotspot and
takes an optional `type` query parameter. The shipped `resources/assets/src/js/hotspot.ts` reads it; a project that
ships its own script reads `hotspot` out of the envelope and appends `flashes` to `#flashes`. The script reads the CSRF
token from `#wrap`'s `hx-headers:inherited`.

### Trail and admin naming

`getTrailModelName()`, `getTrailModelType()`, `getTrailModelAdminRoute()` and `getDisplayName()` are
`getAdminName()`, `getAdminType()` and `getAdminRoute()` from the skeleton `AdminModelInterface`. An unnamed hotspot
reads "Hotspot #2" after its `position`, not "[ No title ]", and `getHtmlId()` is always `hotspot-<id>`.

### Frontend preload

The container override of the cms `EntrySiteRelationsBuilder` is gone; `Events\HotspotEntrySiteRelationsEventHandler`
listens on `Hirtz\Cms\Models\Actions\PreloadEntrySiteRelations::EVENT_AFTER_LOAD_ASSETS` and populates the `hotspots`
relation of every asset that `allowsHotspots()`. A project that extended the builder moves its logic to a listener of
its own.

## Data and schema

The v2 → v3 migrations live in `davidhirtz/yii2-upgrade` under `migrations/yii2-cms-hotspot/`; the bundle itself ships
only `Migrations\M260101000500CmsHotspotBaseline` for fresh installs. Yii sorts every namespace by timestamp, so they
interleave with the skeleton, media and cms migrations and expect the media `asset` table and the cms `cms_asset`
legacy table to exist. In order:

1. `M260910120000Translations` moves the `_xx` columns of `hotspot` into the skeleton `translation` table.
2. `M260911110000CustomAttributes` adds `custom_attributes` to `hotspot` and `hotspot_asset`.
3. `M260912120000Assets` re-points `hotspot.asset_id` at `asset`, copies `cms_asset.hotspot_count` into
   `asset.hotspot_count`, copies every `hotspot_asset` row into `asset` with its id shifted past the highest asset id,
   its text columns and their translations into `custom_attributes`, shifts the `trail` rows of `HotspotAsset` in
   `model_id` and in the parents' `data`, folds `file.hotspot_asset_count` into `file.asset_count` and drops that
   column, then asserts the copy row by row. `hotspot_asset` is kept as the reference it was validated against. The
   id shift must run exactly once: `safeDown()` returns `false`.
4. `M260915110000CustomAttributes` moves `hotspot.name`, `content` and `link`, with the translation rows the first
   migration created for them, into `custom_attributes` and drops the columns.
5. `M260915160000CustomAttributesColumn` moves `custom_attributes` after `y` (column order only).
6. `M260918100000Position` numbers every hotspot per asset in the order it was placed, since v2 never assigned a
   `position`. Not reverted by `safeDown()`.

Before: back up the database, `Db\Connection::$backupOnMigration` does that on the first `migrate`. After: `./yii
search/rebuild` indexes the hotspots and hotspot assets. Lost: a `hotspot_asset` value stored under `embed_url` is
copied but never read; the `hotspot_asset` table and its foreign keys still cascade on `hotspot` and `file` deletes and
must not be read by application code.

## Removed

- The `{hotspots}` template token and `Canvas::$template`.
- `HotspotAssetController::actionDuplicate()`: a hotspot holds a file once, the picker toggles between `create` and `remove`.
- `HotspotSubmenu`'s "back" item to the entry or section; the header's breadcrumbs and the submenu's back link carry it.
- The `zh-CN` and `zh-TW` message files.
- The `hotspot_asset_count` file column and its per-language variants.
