<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\Columns\HotspotAssetThumbnailColumn;
use Hirtz\Cms\Models\Entry;
use Hirtz\Cms\Models\Section;
use Hirtz\Cms\Modules\ModuleTrait;
use Hirtz\Media\Models\File;
use Hirtz\Media\Modules\Admin\Widgets\Grids\Traits\AssetGridViewTrait;
use Hirtz\Media\Modules\Admin\Widgets\Grids\Traits\FileGridViewTrait;
use Hirtz\Skeleton\Helpers\Html;
use Hirtz\Skeleton\Html\Button;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Widgets\Grids\Columns\ButtonColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\DraggableSortGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\ViewGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Hirtz\Skeleton\Widgets\Grids\Toolbars\GridToolbarItem;
use Hirtz\Skeleton\Widgets\Grids\Traits\StatusGridViewTrait;
use Hirtz\Skeleton\Widgets\Grids\Traits\TypeGridViewTrait;
use Stringable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecordInterface;

/**
 * @template T of HotspotAsset
 * @extends GridView<T>
 */
class HotspotAssetGridView extends GridView
{
    use AssetGridViewTrait;
    use FileGridViewTrait;
    use ModuleTrait;
    use StatusGridViewTrait;
    use TypeGridViewTrait;

    public string $layout = '{header}{items}{footer}';

    protected Hotspot $parent;

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
                ->content(Div::make()
                    ->class('form-content btn-group')
                    ->content(...$this->getFooterButtons())),
        ];

        $this->orderRoute = ['/admin/hotspot-asset/order', 'id' => $this->parent->id];

        parent::configure();
    }

    protected function getNameColumn(): array
    {
        return [
            'attribute' => HotspotAsset::instance()->getI18nAttributeName('name'),
            'content' => function (HotspotAsset $asset) {
                $name = $asset->getI18nAttribute('name');
                $route = $this->getRoute($asset);

                $tag = Div::make()
                    ->class($name ? 'strong' : 'text-muted')
                    ->text($name ?: $asset->file->name);

                return $route ? Html::a($tag, $route) : $tag;
            }
        ];
    }

    protected function getThumbnailColumn(): array
    {
        return [
            'class' => HotspotAssetThumbnailColumn::class,
            'route' => fn (HotspotAsset $asset) => $this->getRoute($asset),
        ];
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
            ->text(Yii::t('cms', 'Link assets'))
            ->icon('images')
            ->href(['/admin/hotspot-asset/index', 'hotspot' => $this->parent->id]);
    }

    /**
     * @param HotspotAsset $model
     */
    protected function getRoute(ActiveRecordInterface $model, array $params = []): array|false
    {
        return [...$model->getAdminRoute(), ...$params];
    }

    protected function getFileUploadRoute(): array
    {
        return ['/admin/hotspot-asset/create', 'hotspot' => $this->parent->id];
    }
}
