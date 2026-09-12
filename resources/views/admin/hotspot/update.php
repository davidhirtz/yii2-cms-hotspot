<?php

declare(strict_types=1);

/**
 * @see HotspotController::actionUpdate()
 *
 * @var View $this
 * @var Hotspot $hotspot
 */

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotController;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms\HotspotActiveForm;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\HotspotAssetGridView;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Panels\HotspotPanel;
use Hirtz\Media\Modules\Admin\Data\AssetArrayDataProvider;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\DeleteActiveForm;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;

$this->title(Yii::t('hotspot', 'Edit Hotspot'));

echo HotspotSubmenu::make()
    ->hotspot($hotspot);

echo FormContainer::make()
    ->form(HotspotActiveForm::make()
        ->model($hotspot));

if ($hotspot->hasAssetsEnabled()) {
    echo GridContainer::make()
        ->title(Yii::t('cms', 'Assets'))
        ->grid(HotspotAssetGridView::make()
            ->provider(Yii::$container->get(AssetArrayDataProvider::class, config: [
                'model' => $hotspot,
            ])));
}

echo HotspotPanel::make()
    ->model($hotspot);

echo FormContainer::make()
    ->danger()
    ->title(Yii::t('hotspot', 'Delete Hotspot'))
    ->form(DeleteActiveForm::make()
        ->model($hotspot));
