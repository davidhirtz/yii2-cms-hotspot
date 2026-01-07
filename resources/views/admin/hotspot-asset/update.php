<?php

declare(strict_types=1);

/**
 * @see HotspotAssetController::actionUpdate()
 *
 * @var View $this
 * @var HotspotAsset $asset
 */

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms\HotspotAssetActiveForm;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\DeleteActiveForm;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;

$this->title(Yii::t('hotspot', 'Edit Hotspot Asset'));

echo HotspotSubmenu::make()
    ->hotspot($asset->hotspot);

$this->addBreadcrumb(Yii::t('cms', 'Asset'));

echo FormContainer::make()
    ->title($this->title)
    ->form(HotspotAssetActiveForm::make()
        ->model($asset));

echo FormContainer::make()
    ->danger()
    ->title(Yii::t('cms', 'Remove Asset'))
    ->form(DeleteActiveForm::make()
        ->message(Yii::t('cms', 'Notice: Removing an asset will not delete the actual file.'))
        ->model($asset));

if (Yii::$app->getUser()->can('fileDelete', ['file' => $asset->file])) {
    echo FormContainer::make()
        ->danger()
        ->title(Yii::t('media', 'Delete File'))
        ->form(DeleteActiveForm::make()
            ->model($asset->file)
            ->action(['/admin/file/delete', 'id' => $asset->file_id])
            ->message(Yii::t('cms', 'Warning: Deleting this file cannot be undone. All related assets will also be unrecoverably deleted. Please be certain!')));
}
