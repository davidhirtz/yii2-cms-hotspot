<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\Models\Entry;
use Hirtz\Cms\Models\Section;
use Hirtz\Cms\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn;
use Hirtz\Cms\Modules\ModuleTrait;
use Hirtz\Media\Models\File;
use Hirtz\Media\Modules\Admin\Widgets\Grids\Traits\AssetGridViewTrait;
use Hirtz\Media\Modules\Admin\Widgets\Buttons\FileButtonsTrait;
use Hirtz\Skeleton\Html\A;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Buttons\ButtonGroup;
use Hirtz\Skeleton\Widgets\Grids\Columns\ButtonColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\DraggableSortGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\ViewGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\Columns\DataColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\StatusIconColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\TypeColumn;
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Hirtz\Skeleton\Widgets\Grids\Toolbars\GridToolbarItem;
use Override;
use Stringable;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * @template T of HotspotAsset
 * @extends GridView<T>
 *
 * @property Hotspot $parent
 */
class HotspotAssetGridView extends GridView
{
    use AssetGridViewTrait;
    use FileButtonsTrait;
    use ModuleTrait;

    protected string $layout = '{header}{items}{footer}';

    #[Override]
    protected function configure(): void
    {
        $this->provider ??= new ActiveDataProvider([
            'query' => $this->getParentAssetQuery(),
            'pagination' => false,
            'sort' => false,
        ]);

        $this->columns ??= [
            $this->getStatusColumn(),
            $this->getThumbnailColumn(),
            $this->getTypeColumn(),
            $this->getNameColumn(),
            $this->getDimensionsColumn(),
            $this->getButtonColumn(),
        ];

        $this->footer ??= [
            GridToolbarItem::make()
                ->class('form-row')
                ->content(ButtonGroup::make()
                    ->class('form-content')
                    ->content(...$this->getFooterButtons())),
        ];

        $this->orderRoute = ['/admin/hotspot-asset/order', 'id' => $this->parent->id];

        parent::configure();
    }

    protected function getStatusColumn(): ?Column
    {
        return StatusIconColumn::make();
    }

    protected function getTypeColumn(): ?Column
    {
        return TypeColumn::make()
            ->url(fn (HotspotAsset $model) => $model->getAdminRoute())
            ->visible($this->hasVisibleTypes());
    }

    protected function hasVisibleTypes(): bool
    {
        return count(HotspotAsset::instance()::getTypes()) > 1;
    }

    protected function getNameColumn(): ?Column
    {
        return DataColumn::make()
            ->property(HotspotAsset::instance()->getI18nAttributeName('name'))
            ->content($this->getNameColumnContent(...));
    }

    protected function getNameColumnContent(HotspotAsset $asset): ?Stringable
    {
        $name = $asset->getI18nAttribute('name');
        $route = $asset->getAdminRoute();

        $content = $name
            ? Div::make()
                ->class('strong')
                ->text($name)
            : Div::make()
                ->class('text-muted')
                ->text($asset->file->name);

        return $route
            ? A::make()
                ->content($content)
                ->href($route)
            : $content;
    }

    protected function getThumbnailColumn(): ?Column
    {
        return AssetThumbnailColumn::make()
            ->url(fn (HotspotAsset $asset) => $asset->getAdminRoute());
    }


    protected function getButtonColumn(): ?Column
    {
        return ButtonColumn::make()
            ->content($this->getButtonColumnContent(...));
    }

    protected function getButtonColumnContent(HotspotAsset $asset): array
    {
        $user = Yii::$app->getUser();
        $buttons = [];

        if ($this->isSortable() && $this->provider->getCount() > 1) {
            $buttons[] = DraggableSortGridButton::make();
        }

        if ($user->can(File::AUTH_FILE_UPDATE, ['file' => $asset->file])) {
            $buttons[] = $this->getFileUpdateButton($asset);
        }

        $permission = $this->parent->asset->isEntryAsset()
            ? Entry::AUTH_ENTRY_ASSET_UPDATE
            : Section::AUTH_SECTION_ASSET_UPDATE;

        if ($user->can($permission, ['asset' => $asset])) {
            $buttons[] = ViewGridButton::make()
                ->model($asset);
        }

        $permission = $this->parent->asset->isEntryAsset()
            ? Entry::AUTH_ENTRY_ASSET_DELETE
            : Section::AUTH_SECTION_ASSET_DELETE;

        if ($user->can($permission, ['asset' => $asset])) {
            $buttons[] = $this->getDeleteButton($asset);
        }

        return $buttons;
    }

    /**
     * @see HotspotAssetController::actionCreate()
     */
    protected function getFooterButtons(): array
    {
        $user = Yii::$app->getUser();
        $parent = $this->parent->asset->parent;
        $buttons = [];

        $hasPermission = $parent instanceof Entry
            ? $user->can(Entry::AUTH_ENTRY_ASSET_CREATE, ['entry' => $parent])
            : $user->can(Section::AUTH_SECTION_ASSET_CREATE, ['section' => $parent]);

        if ($hasPermission) {
            if ($user->can(File::AUTH_FILE_CREATE)) {
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
            ->text(Lang::t('cms', 'COMMON_LINK_ASSETS'))
            ->icon('images')
            ->url(['/admin/hotspot-asset/index', 'hotspot' => $this->parent->id]);
    }

    protected function getFileUploadRoute(): array
    {
        return ['/admin/hotspot-asset/create', 'hotspot' => $this->parent->id];
    }
}
