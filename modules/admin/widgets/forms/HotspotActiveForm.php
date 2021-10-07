<?php

namespace davidhirtz\yii2\hotspot\modules\admin\widgets\forms;

use davidhirtz\yii2\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ContentFieldTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ModelTimestampTrait;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveField;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveForm;
use davidhirtz\yii2\skeleton\widgets\forms\DynamicRangeDropdown;

/**
 * Class HotspotActiveForm
 * @package davidhirtz\yii2\hotspot\modules\admin\widgets\forms
 *
 * @property Hotspot $model
 */
class HotspotActiveForm extends ActiveForm
{
    use ModuleTrait;
    use ModelTimestampTrait;
    use ContentFieldTrait;

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
                ['status', DynamicRangeDropdown::class],
                ['type', DynamicRangeDropdown::class],
                'name',
                'content',
                'link',
                'x',
                'y',
            ];
        }

        parent::init();
    }

    /**
     * @param array $options
     * @return ActiveField
     */
    public function xField($options = [])
    {
        return $this->getCoordinateField('x', $options);
    }

    /**
     * @param array $options
     * @return ActiveField
     */
    public function yField($options = [])
    {
        return $this->getCoordinateField('y', $options);
    }

    /**
     * @param string $attribute
     * @param array $options
     * @return ActiveField
     */
    protected function getCoordinateField($attribute, $options = [])
    {
        return $this->field($this->model, $attribute, $options)->appendInput('%');
    }
}