## 2.1.4 (under development)

- Fixed error in `Canvas::renderHotspots()` if no hotspots are available
- Fixed override of definitions in `Bootstrap`

## 2.1.3 (Nov 18, 2024)

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
- Removed `Hotspot::updateAssetOrder()`, use `\davidhirtz\yii2\cms\hotspot\models\actions\ReorderHotspotAsset`
  instead
- Removed `Hotspot::clone()` and `HotspotAsset::clone()`,
  use `davidhirtz\yii2\cms\hotspot\models\actions\DuplicateHotspot` instead
- Removed `HotspotAsset::updatePosition()`, use `davidhirtz\yii2\cms\hotspot\models\actions\ReorderHotspotAssets`
  instead

## 2.0.1 (Nov 4, 2023)

- Changed namespaces for model interfaces to `davidhirtz\yii2\media\models\interfaces`

## 2.0.0 (Nov 3, 2023)

- Moved source code to `src` folder
- Moved models and widgets out of `base` folder, to override them use Yii's dependency injection
  container
- Changed namespaces from `davidhirtz\yii2\cms\hotspot\admin\widgets\grid`
  to `davidhirtz\yii2\cms\hotspot\admin\widgets\grids` and `davidhirtz\yii2\cms\hotspot\admin\widgets\nav`
  to `davidhirtz\yii2\cms\hotspot\admin\widgets\navs`
- Added `AssetPreview` as an improved replacement for the default asset preview
- Removed `ActiveForm::getActiveForm()`, to override the active forms, use Yii's dependency injection
  container