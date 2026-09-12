<?php

declare(strict_types=1);

/**
 * @see AbstractAssetController::actionCreate()
 *
 * @var View $this
 * @var Hotspot $model
 * @var FileActiveDataProvider $provider
 */

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Media\Modules\Admin\Controllers\AbstractAssetController;
use Hirtz\Media\Modules\Admin\Data\FileActiveDataProvider;
use Hirtz\Media\Modules\Admin\Widgets\Grids\FileGridView;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;

$this->title(Yii::t('media', 'ASSET_MODEL_LABEL'));

echo HotspotSubmenu::make()
    ->hotspot($model);

$this->addBreadcrumb(Yii::t('media', 'COMMON_LINK_ASSETS'));

echo GridContainer::make()
    ->grid(FileGridView::make()
        ->model($model)
        ->provider($provider));
