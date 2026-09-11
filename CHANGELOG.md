## 3.0.0 (in development)

- `Models\Hotspot` implements the skeleton `Models\Interfaces\AdminRouteInterface` and dropped its
  `getTrailModelAdminRoute()`
- `HotspotAssetController` extends the skeleton `Controller` and uses the media `AssetControllerTrait`; its
  hotspot is addressed as `id`. The file picker moved from `hotspot-asset/index` to `hotspot-asset/create`,
  and `index` lists the hotspot's assets
- `Widgets\Navs\HotspotSubmenu` renders the submenu of the entry or section the hotspot's asset belongs to
  instead of extending `EntrySubmenu` and passing it an asset. It is the only place left that has to know
  which of the two it is
- Fixed the admin routes, which were missing the module segment the controllers are mapped under:
  `/admin/hotspot/hotspot/update`, `/admin/hotspot/hotspot/create` and `/admin/hotspot/hotspot-asset/*`
- `Modules\Admin\Controllers\HotspotAssetController` declares its own access rules and resolves every action
  through `HotspotTrait::findHotspot()`, which is what checks the cms asset the hotspot sits on
- Added `Tests\Migrations\AssetMigrationTest`, which replays `M260912120000Assets` against the kept
  `hotspot_asset` table and pins the id offset in the rows and in both places the trail stores it
- `Models\HotspotAsset` is a subclass of `Hirtz\Media\Models\Asset` on the shared `asset` table, keeping its
  class name so existing trail rows still resolve. It borrows the permissions of the cms asset its hotspot sits on
  and drops the `embed_url` definition. `Models\Hotspot` implements `AssetModelInterface`
- `M260912120000Assets` re-points `hotspot.asset_id` at `asset`, copies `hotspot_asset` with the ids offset past the
  cms ones, shifts its trail rows in both places and folds `file.hotspot_asset_count` into `asset_count`. The id
  shift must run exactly once: nothing marks a row as shifted. It asserts its own result; `safeDown()` returns
  `false`. `hotspot_asset` is kept as the validation reference
- Removed `Models\Queries\HotspotAssetQuery`, `Models\Actions\ReorderHotspotAssets`,
  `Models\Events\FileBeforeDeleteEventHandler`, `Widgets\Forms\HotspotAssetActiveForm`,
  `Widgets\Grids\FileHotspotAssetGrid`, `FileHotspotAssetGridContainer` and `Test\Fixtures\HotspotAssetFixture`.
  `HotspotAssetController` extends the media `AbstractAssetController`, and `Widgets\Grids\HotspotAssetGridView`
  extends the media grid
- The asset duplicate and delete handlers subscribe on `EntryAsset` and `SectionAsset`; the file handler is gone
- `Models\Hotspot` and `Models\HotspotAsset` use `VisibleAttributeTrait` from `Hirtz\Skeleton\Models\Traits`
  instead of `Hirtz\Cms\Models\Traits`
- `Models\Hotspot` implements `CustomAttributeInterface`; `Models\HotspotAsset` inherits it from the cms base. Added
  the `custom_attributes` column to `hotspot` and `hotspot_asset`, excluded from the trail
- The admin forms render the custom attribute fields, and `HotspotController` and `HotspotAssetController` guard their
  save with `Request::isFormReload()`

- Translated attributes of `Hotspot` and `HotspotAsset` moved from their `_xx` columns into the skeleton's
  `translation` table (`M260910120000Translations`)
- `HotspotAssetController::actionOrder()` now returns a flash fragment (was `void`) and emits a success
  flash after a reorder; added the `HOTSPOT_ASSET_SUCCESS_ORDERED` message

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