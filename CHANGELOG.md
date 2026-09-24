## 3.1.0 (September 24, 2026)

- Changed `Hotspot::updateAssetHotspotCount()` to renumber the hotspots `1..n` first, so a delete leaves no gap;
  `M260924100000RenumberHotspotPositions` closes the gaps an installation already holds
- Changed `Artwork::hotspotWrapper()` to stack its closures

## 3.0.0 (September 23, 2026)

- Renamed the namespace from `davidhirtz\yii2\cms\hotspot\` to `Hirtz\Cms\Hotspot\` and every directory to
  StudlyCase (`Models\Hotspot`, `Modules\Admin\Controllers\HotspotController`); requires PHP 8.3 and `yii2-cms` 3.0
- Moved `enableEntryAssetHotspots`, `enableSectionAssetHotspots` and `enableHotspotAssets` from the admin submodule
  to the new base `Module`, configured under `modules.hotspot` instead of `modules.admin.modules.hotspot`
- Changed `Models\HotspotAsset` to a subclass of `Hirtz\Media\Models\Asset` on the shared `asset` table, registered
  through `media.assets` and limited to `name`, `content`, `alt_text` and `link`; removed the `hotspot_asset` model
  table, `file.hotspot_asset_count`, `Models\Queries\HotspotAssetQuery`, `Models\Actions\ReorderHotspotAssets` and
  `Models\Events\FileBeforeDeleteEventHandler`
- Changed `hotspot.name`, `content` and `link` to custom attributes in `custom_attributes`; removed `Hotspot::$contentType`
  and `$htmlValidator`, `link` is validated as a URL, and none of the three is a query condition any more
- Moved the translated attributes of `Hotspot` into the skeleton `translation` table; a project names them in
  `Hotspot::$translatableAttributes` instead of `$i18nAttributes`
- Added `Models\Types\HotspotType` as the hotspot's type class, carrying the media `sizes()` and `transformations()`
- Replaced `HotspotAsset::FIELD_HOTSPOTS` and the `'#hotspots'` marker with `Module::FIELD_HOTSPOTS` (`'hotspots'`);
  `Module::allowsHotspots()` is the one reader for admin and frontend, and covers block assets under `enableSectionAssetHotspots`
- Renamed `Hotspot::hasAssetsEnabled()` to `allowsAssets()`, which consults the hotspot type as well
- Replaced `Widgets\Canvas` with `Widgets\Artwork` over the cms `Widgets\Artwork`: the `{hotspots}` template token is
  gone, the hotspots wrap the media in a `.relative` `Div`, `hotspotViewFile()` and `hotspotWrapper()` are setters
- Replaced `Models\Builders\EntrySiteRelationsBuilder` with `Events\HotspotEntrySiteRelationsEventHandler`, a listener
  on the cms `PreloadEntrySiteRelations::EVENT_AFTER_LOAD_ASSETS` instead of a container override
- Renamed `Modules\Admin\Widgets\Forms\Fields\AssetPreview` to `AssetPreviewField` and `Assets\AdminAsset` to
  `Assets\HotspotAdminAssetBundle`; the container keys are the media `AssetPreviewField` and `AssetThumbnailColumn`
- Changed the admin routes to `/admin/hotspot/hotspot/*` and `/admin/hotspot/hotspot-asset/*`; the controllers require
  `Entry::AUTH_ENTRY` or `Block::AUTH_BLOCK` instead of `entryAssetUpdate` and `sectionAssetUpdate`
- Changed `HotspotController::actionCreate()` to answer `{hotspot, flashes}` as JSON and to take an optional `type`
- Replaced `HotspotAssetController::actionDuplicate()` with `actionRemove()`; added `actionStatus()` and `actionDeleteAll()`
- Removed `Modules\Admin\Widgets\Grids\HotspotAssetGridView`, `HotspotAssetParentGridView`,
  `Columns\HotspotAssetThumbnailColumn`, `Panels\HotspotAssetFilePanel`, `Panels\HotspotHelpPanel` and
  `Forms\HotspotAssetActiveForm`; the hotspot's assets are listed on `hotspot-asset/index` by the media `AssetGridView`
- Added `Modules\Admin\Widgets\Navs\HotspotHeader` and `HotspotActionDropdown`; `HotspotSubmenu` extends the skeleton
  `Submenu`, and header and submenu take the hotspot through `model()`
- Changed `Hotspot` and `HotspotAsset` to implement the skeleton `AdminModelInterface`: `getTrailModelName()`,
  `getTrailModelType()`, `getTrailModelAdminRoute()` and `getDisplayName()` are `getAdminName()`, `getAdminType()`
  and `getAdminRoute()`; an unnamed hotspot is named by its `position`, which every hotspot now carries
- Changed `Hotspot::getHtmlId()` to always answer `hotspot-<id>` and `Hotspot::getModule()` to answer the hotspot module
- Changed the message keys to domain-first constants (`HOTSPOT_SUCCESS_UPDATED`, `HOTSPOT_X_LABEL`); removed `zh-CN` and `zh-TW`
- Added `Hotspot` and `HotspotAsset` to the `search` component; run `./yii search/rebuild` once

## 2.3.0 (Oct 21, 2025)

- Requires PHP 8.3

## 2.2.1 (Jan 28, 2025)

- Changed `Bootstrap` I18N configuration

## 2.2.0 (Nov 29, 2024)

- Added `HotspotAssetFilePanel` for `yii2-media` version 2.2 to display I18N assets in file view
- Added `FileBeforeDeleteEventHandler`
- Removed `HotspotAsset::getParentName()`
- Renamed `HotspotAsset::getParentGridView()` to `HotspotAsset::getFilePanelClass()`
- Replaced `HotspotAsset::updateOrDeleteFileByAssetCount()` with `HotspotAsset::updateFileRelatedCount()`
- Replaced `HotspotAsset::getFileCountAttribute()` with `HotspotAsset::getFileCountAttributeNames()`

## 2.1.6 (Nov 19, 2024)

- Added `TypeAttributeInterface` to `Hotspot` to reflect the changes in `yii2-media` Version 2.1.26

## 2.1.5 (Nov 19, 2024)

- Fixed `EntrySiteRelationsBuilder` to populate hotspot relation for hotspot assets
- Fixed error in `Canvas::renderHotspots()` if no hotspots are available
- Fixed override of definitions in `Bootstrap`

## 2.1.4 (Nov 18, 2024)

- Updated dependencies and tests

## 2.1.3 (Jan 9, 2024)

- Added `Hotspot::getVisibleAssets()`

## 2.1.2 (Jan 9, 2024)

- Fixed Rector (Issue #5)

## 2.1.1 (Jan 8, 2024)

- Added `HotspotAssetThumbnailColumn` to better differentiate between hotspot assets and regular assets in the grid view
- Fixed sorting hotspot asset bug

## 2.1.0 (Dec 20, 2023)

- Added Codeception test suite
- Added GitHub Actions CI workflow

## 2.0.5 (Nov 28, 2023)

- Changed the duplicate actions to keep the status of related records on duplicate
- Changed `Hotspot::afterSave()` to always update the parent asset on changed attributes

## 2.0.4 (Nov 6, 2023)

- Changed the default view path of `Canvas`

## 2.0.3 (Nov 6, 2023)

- Updated `AssetParentTrait` namespace

## 2.0.2 (Nov 6, 2023)

- Moved `Bootstrap` class to base package namespace for consistency
- Removed `SiteController`, instead the implementation of `EntrySiteRelationsBuilder` is extended, which takes care of
  loading hotspots and assets
- Removed `Hotspot::updateAssetOrder()`, use `\Hirtz\Cms\Hotspot\Models\Actions\ReorderHotspotAsset`
  instead
- Removed `Hotspot::clone()` and `HotspotAsset::clone()`,
  use `Hirtz\Cms\Hotspot\Models\Actions\DuplicateHotspot` instead
- Removed `HotspotAsset::updatePosition()`, use `Hirtz\Cms\Hotspot\Models\Actions\ReorderHotspotAssets`
  instead

## 2.0.1 (Nov 4, 2023)

- Changed namespaces for model interfaces to `Hirtz\Media\Models\Interfaces`

## 2.0.0 (Nov 3, 2023)

- Moved source code to `src` folder
- Moved models and widgets out of `base` folder, to override them use Yii's dependency injection
  container
- Changed namespaces from `Hirtz\Cms\Hotspot\admin\widgets\grid`
  to `Hirtz\Cms\Hotspot\admin\widgets\grids` and `Hirtz\Cms\Hotspot\admin\widgets\nav`
  to `Hirtz\Cms\Hotspot\admin\widgets\navs`
- Added `AssetPreview` as an improved replacement for the default asset preview
- Removed `ActiveForm::getActiveForm()`, to override the active forms, use Yii's dependency injection
  container