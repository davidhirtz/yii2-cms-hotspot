<?php

namespace davidhirtz\yii2\hotspot\modules\admin\widgets\grid\traits;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\modules\admin\widgets\grid\AssetGridView;
use davidhirtz\yii2\skeleton\helpers\Html;
use Yii;

/**
 * Trait ThumbnailRowTrait
 *
 * This can be used in {@link AssetGridView} to display the current hotspot count.
 */
trait ThumbnailRowTrait
{
    /**
     * @return array
     */
    public function thumbnailColumn(): array
    {
        return [
            'headerOptions' => ['style' => 'width:150px'],
            'content' => function (Asset $asset) {
                if (!$asset->file->hasPreview()) {
                    return '';
                }

                $content = Html::tag('div', '', [
                    'style' => 'background-image:url(' . ($asset->file->getTransformationUrl('admin') ?: $asset->file->getUrl()) . ');',
                    'class' => 'thumb',
                ]);

                if ($hotspotCount = $asset->hotspot_count) {
                    $badge = Html::tag('div', $hotspotCount, [
                        'class' => 'btn btn-primary btn-sm',
                        'style' => 'position: absolute; top: 5px; left: 5px;',
                    ]);

                    $content = Html::tag('div', $content . $badge, [
                        'class' => 'active',
                        'style' => 'position:relative',
                    ]);
                }

                $isLink = Yii::$app->getUser()->can($asset->isEntryAsset() ? 'entryAssetUpdate' : 'sectionAssetUpdate', [
                    'asset' => $asset,
                ]);

                return $isLink ? Html::a($content, $this->getRoute($asset)) : $content;
            }
        ];
    }

}