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