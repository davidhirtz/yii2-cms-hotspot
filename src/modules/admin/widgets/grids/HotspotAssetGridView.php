<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids\columns\HotspotAssetThumbnailColumn;
use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\media\modules\admin\widgets\grids\traits\AssetColumnsTrait;
use davidhirtz\yii2\media\modules\admin\widgets\grids\traits\UploadTrait;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\GridView;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\traits\StatusGridViewTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\traits\TypeGridViewTrait;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecordInterface;

/**
 * @extends GridView<HotspotAsset>
 * @property Hotspot $parent
 */
class HotspotAssetGridView extends GridView
{
    use AssetColumnsTrait;
    use ModuleTrait;
    use StatusGridViewTrait;
    use TypeGridViewTrait;
    use UploadTrait;

    public $layout = '{header}{items}{footer}';

    public function init(): void
    {
        $this->dataProvider ??= new ActiveDataProvider([
            'query' => $this->getParentAssetQuery(),
            'pagination' => false,
            'sort' => false,
        ]);

        if (!$this->columns) {
            $this->columns = [
                $this->statusColumn(),
                $this->thumbnailColumn(),
                $this->typeColumn(),
                $this->nameColumn(),
                $this->dimensionsColumn(),
                $this->buttonsColumn(),
            ];
        }

        if (Yii::$app->getUser()->can('fileCreate')) {
            $this->registerAssetClientScripts();
        }

        $this->orderRoute = ['/admin/hotspot-asset/order', 'id' => $this->parent->id];

        parent::init();
    }

    protected function initFooter(): void
    {
        $this->footer ??= [
            [
                [
                    'content' => Html::buttons($this->getFooterButtons()),
                    'options' => ['class' => 'offset-md-3 col-md-9'],
                ],
            ],
        ];
    }

    public function renderItems(): string
    {
        return Html::tag('div', parent::renderItems(), ['id' => 'files']);
    }

    public function buttonsColumn(): array
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => fn (HotspotAsset $asset): string => Html::buttons($this->getRowButtons($asset))
        ];
    }

    public function nameColumn(): array
    {
        return [
            'attribute' => $this->getModel()->getI18nAttributeName('name'),
            'content' => function (HotspotAsset $asset) {
                $name = $asset->getI18nAttribute('name');
                $route = $this->getRoute($asset);

                $tag = $name
                    ? Html::tag('strong', Html::encode($asset->getI18nAttribute('name')))
                    : Html::tag('span', Html::encode($asset->file->name), ['class' => 'text-muted']);

                return $route ? Html::a($tag, $route) : $tag;
            }
        ];
    }
    public function thumbnailColumn(): array
    {
        return [
            'class' => HotspotAssetThumbnailColumn::class,
            'route' => fn (HotspotAsset $asset) => $this->getRoute($asset),
        ];
    }

    protected function getFooterButtons(): array
    {
        $user = Yii::$app->getUser();
        $parent = $this->parent->asset->parent;
        $buttons = [];

        $hasPermission = $parent instanceof Entry
            ? $user->can('entryAssetCreate', ['entry' => $parent])
            : $user->can('sectionAssetCreate', ['section' => $parent]);

        if ($hasPermission) {
            if ($user->can('fileCreate')) {
                $buttons[] = $this->getUploadFileButton();
                $buttons[] = $this->getImportFileButton();
            }

            $buttons[] = $this->getAssetsButton();
        }

        return $buttons;
    }

    protected function getAssetsButton(): string
    {
        $text = Html::iconText('images', Yii::t('cms', 'Link assets'));

        return Html::a($text, ['/admin/hotspot-asset/index', 'hotspot' => $this->parent->id], [
            'class' => 'btn btn-primary',
        ]);
    }

    protected function getRowButtons(HotspotAsset $asset): array
    {
        $user = Yii::$app->getUser();
        $buttons = [];

        if ($this->isSortedByPosition() && $this->dataProvider->getCount() > 1) {
            $buttons[] = $this->getSortableButton();
        }

        if ($user->can('fileUpdate', ['file' => $asset->file])) {
            $buttons[] = $this->getFileUpdateButton($asset);
        }

        $buttons[] = $this->getUpdateButton($asset);
        $buttons[] = $this->getDeleteButton($asset);

        return $buttons;
    }

    /**
     * @param HotspotAsset $model
     */
    protected function getRoute(ActiveRecordInterface $model, array $params = []): array|false
    {
        return array_merge($model->getAdminRoute(), $params);
    }

    protected function getFileUploadRoute(): array
    {
        return ['/admin/hotspot-asset/create', 'hotspot' => $this->parent->id];
    }

    protected function getDeleteRoute(ActiveRecordInterface $model, array $params = []): array
    {
        return ['/admin/hotspot-asset/delete', 'id' => $model->getPrimaryKey(), ...$params];
    }
}
