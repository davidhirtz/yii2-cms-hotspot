<?php

namespace davidhirtz\yii2\annotation\modules\admin\widgets\grid\base;

use davidhirtz\yii2\annotation\models\Annotation;
use davidhirtz\yii2\annotation\models\AnnotationAsset;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grid\GridView;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\timeago\Timeago;
use yii\data\ArrayDataProvider;
use Yii;

/**
 * Class AnnotationAssetParentGridView
 * @package davidhirtz\yii2\annotation\modules\admin\widgets\grid\base
 */
class AnnotationAssetParentGridView extends GridView
{
    /**
     * @var File
     */
    public $file;

    /**
     * @var bool
     */
    public $showHeader = false;

    /**
     * @var string
     */
    public $layout = '{items}';

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (!$this->dataProvider) {
            $this->dataProvider = new ArrayDataProvider([
                'allModels' => AnnotationAsset::find()
                    ->where(['file_id' => $this->file->id])
                    ->with(['annotation'])
                    ->orderBy(['updated_at' => SORT_DESC])
                    ->all()
            ]);
        }

        if (!$this->columns) {
            $this->columns = [
                $this->statusColumn(),
                $this->typeColumn(),
                $this->nameColumn(),
                $this->assetCountColumn(),
                $this->updatedAtColumn(),
                $this->buttonsColumn(),
            ];
        }

        /** @var AnnotationAsset $asset */
        foreach ($this->dataProvider->getModels() as $asset) {
            $asset->populateRelation('file', $this->file);
        }

        parent::init();
    }

    /**
     * @return array
     */
    public function statusColumn()
    {
        return [
            'contentOptions' => ['class' => 'text-center'],
            'content' => function (AnnotationAsset $asset) {
                return Icon::tag($asset->annotation->getStatusIcon(), [
                    'data-toggle' => 'tooltip',
                    'title' => $asset->annotation->getStatusName(),
                ]);
            }
        ];
    }

    /**
     * @return array
     */
    public function typeColumn()
    {
        return [
            'attribute' => 'type',
            'contentOptions' => ['class' => 'text-nowrap'],
            'visible' => count(Annotation::getTypes()) > 1,
            'content' => function (AnnotationAsset $asset) {
                return ($route = $this->getRoute($asset)) ? Html::a($asset->annotation->getTypeName(), $route) : $asset->annotation->getTypeName();
            }
        ];
    }

    /**
     * @return array
     */
    public function nameColumn()
    {
        return [
            'content' => function (AnnotationAsset $asset) {
                return Html::tag('strong', Html::a(Yii::t('annotation', 'Annotation'), $this->getRoute($asset)));
            }
        ];
    }

    /**
     * @return array
     */
    public function assetCountColumn()
    {
        return [
            'headerOptions' => ['class' => 'd-none d-md-table-cell text-center'],
            'contentOptions' => ['class' => 'd-none d-md-table-cell text-center'],
            'content' => function (AnnotationAsset $asset) {
                return Html::a(Yii::$app->getFormatter()->asInteger($asset->annotation->asset_count), $this->getRoute($asset), ['class' => 'badge']);
            }
        ];
    }

    /**
     * @return array
     */
    public function updatedAtColumn()
    {
        return [
            'headerOptions' => ['class' => 'd-none d-lg-table-cell'],
            'contentOptions' => ['class' => 'd-none d-lg-table-cell text-nowrap'],
            'content' => function (AnnotationAsset $asset) {
                return Timeago::tag($asset->updated_at);
            }
        ];
    }

    /**
     * @return array
     */
    public function buttonsColumn()
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => function (AnnotationAsset $asset) {
                $user = Yii::$app->getUser();
                $buttons = [];

                if ($user->can('annotationUpdate', ['asset' => $asset])) {
                    $buttons[] = Html::a(Icon::tag('wrench'), ['admin/annotation/update', 'id' => $asset->id], [
                        'class' => 'btn btn-primary',
                        'data-toggle' => 'tooltip',
                        'title' => Yii::t('annotation', 'Edit Asset'),
                    ]);
                }

                if ($user->can('annotationUpdate', ['asset' => $asset])) {
                    $buttons[] = Html::a(Icon::tag('trash'), ['admin/annotation-asset/delete', 'id' => $asset->id], [
                        'class' => 'btn btn-danger btn-delete-asset d-none d-md-inline-block',
                        'data-confirm' => Yii::t('annotation', 'Are you sure you want to remove this asset?'),
                        'data-ajax' => 'remove',
                        'data-target' => '#' . $this->getRowId($asset),
                    ]);
                }

                return Html::buttons($buttons);
            }
        ];
    }

    /**
     * @param AnnotationAsset $model
     * @param array $params
     * @return array|false
     */
    protected function getRoute($model, $params = [])
    {
        if ( Yii::$app->getUser()->can('annotationUpdate', ['annotation' => $model->annotation])) {
            return array_merge(['/admin/annotation/update', 'id' => $model->annotation_id], $params);
        }

        return false;
    }

    /**
     * @return AnnotationAsset
     */
    public function getModel()
    {
        return AnnotationAsset::instance();
    }
}