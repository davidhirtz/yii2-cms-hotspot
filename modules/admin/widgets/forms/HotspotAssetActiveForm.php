<?php

namespace davidhirtz\yii2\hotspot\modules\admin\widgets\forms;

use davidhirtz\yii2\cms\modules\admin\widgets\forms\ActiveForm;
use davidhirtz\yii2\hotspot\models\base\HotspotAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class HotspotAssetActiveForm
 * @package davidhirtz\yii2\cms\modules\admin\widgets\forms
 *
 * @property HotspotAsset $model
 */
class HotspotAssetActiveForm extends ActiveForm
{
    /**
     * @var bool
     */
    public $hasStickyButtons = true;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (!$this->fields) {
            $this->fields = [
                'status',
                'type',
                'name',
                'content',
                'alt_text',
                'link',
            ];
        }

        parent::init();
    }

    /**
     * @inheritDoc
     */
    public function renderHeader()
    {
        if ($previewField = $this->previewField()) {
            echo $previewField;
            echo $this->horizontalLine();
        }

        parent::renderHeader();
    }

    /**
     * @return string
     */
    public function previewField()
    {
        $file = $this->model->file;

        if ($file->hasPreview()) {
            $image = Html::img($file->getUrl(), [
                'id' => 'image',
                'class' => 'img-transparent',
            ]);

            return $this->row($this->offset(!($width = $this->model->file->width) ? $image : Html::tag('div', $image, [
                'style' => "max-width:{$width}px",
            ])));
        }

        return '';
    }

    /**
     * @param array $options
     * @return string
     */
    public function altTextField($options = [])
    {
        $language = ArrayHelper::remove($options, 'language');
        $attribute = $this->model->getI18nAttributeName('alt_text', $language);

        if (!isset($options['inputOptions']['placeholder'])) {
            $options['inputOptions']['placeholder'] = $this->model->file->getI18nAttribute('alt_text', $language);
        }

        return $this->field($this->model, $attribute, $options);
    }
}