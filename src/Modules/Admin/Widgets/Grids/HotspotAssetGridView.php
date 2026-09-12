<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Media\Models\File;
use Hirtz\Media\Modules\Admin\Widgets\Buttons\FileButtonsTrait;
use Hirtz\Media\Modules\Admin\Widgets\Grids\AssetGridView;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetSubmenuItem;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Buttons\ButtonGroup;
use Hirtz\Skeleton\Widgets\Grids\Toolbars\GridToolbarItem;
use Override;
use Stringable;
use Yii;

/**
 * Embedded in the hotspot update page, so it carries its own footer buttons instead of a header dropdown.
 *
 * @template T of HotspotAsset
 * @extends AssetGridView<T>
 */
class HotspotAssetGridView extends AssetGridView
{
    use FileButtonsTrait;

    protected string $layout = '{header}{items}{footer}';

    #[Override]
    protected function configure(): void
    {
        $this->footer ??= [
            GridToolbarItem::make()
                ->class('form-row')
                ->content(ButtonGroup::make()
                    ->class('form-content')
                    ->content(...$this->getFooterButtons())),
        ];

        parent::configure();
    }

    /**
     * @see HotspotAssetController::actionCreate()
     * @return list<Stringable>
     */
    protected function getFooterButtons(): array
    {
        /** @var Hotspot $hotspot */
        $hotspot = $this->provider->model;
        $asset = $hotspot->asset;
        $buttons = [];

        if ($this->webuser->can($asset->getPermissionName('create'), ['asset' => $asset])) {
            if ($this->webuser->can(File::AUTH_FILE_CREATE)) {
                $buttons[] = $this->getFileUploadButton();
                $buttons[] = $this->getFileImportButton();
            }

            $buttons[] = $this->getAssetLinkButton();
        }

        return $buttons;
    }

    protected function getAssetLinkButton(): ?Stringable
    {
        return Button::make()
            ->primary()
            ->text(Yii::t('media', 'COMMON_LINK_ASSETS'))
            ->icon('images')
            ->url($this->getFileUploadRoute());
    }

    #[Override]
    protected function getFileUploadRoute(): array
    {
        /** @var Hotspot $hotspot */
        $hotspot = $this->provider->model;

        return HotspotAsset::getAdminCreateRoute($hotspot);
    }

    #[Override]
    protected function getFileUploadTarget(): string
    {
        return '#' . AssetGridView::ID;
    }

    protected function getFileUploadSelectOob(): ?string
    {
        return '#' . AssetSubmenuItem::ID;
    }
}
