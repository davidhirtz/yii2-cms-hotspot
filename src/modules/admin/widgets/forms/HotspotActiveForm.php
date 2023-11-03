<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\forms;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ContentFieldTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ModelTimestampTrait;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveField;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveForm;
use davidhirtz\yii2\skeleton\widgets\forms\DynamicRangeDropdown;

/**
 * @property Hotspot $model
 */
class HotspotActiveForm extends ActiveForm
{
    use ContentFieldTrait;
    use ModuleTrait;
    use ModelTimestampTrait;

    public bool $hasStickyButtons = true;

    public function init(): void
    {
        $this->fields ??= [
            ['status', DynamicRangeDropdown::class],
            ['type', DynamicRangeDropdown::class],
            'name',
            'content',
            'link',
            $this->xField(...),
            $this->yField(...),
        ];

        parent::init();
    }

    public function xField(array $options = []): ActiveField|string
    {
        return $this->getCoordinateField('x', $options);
    }

    public function yField(array $options = []): ActiveField|string
    {
        return $this->getCoordinateField('y', $options);
    }

    protected function getCoordinateField(string $attribute, array $options = []): ActiveField|string
    {
        return $this->field($this->model, $attribute, $options)->appendInput('%');
    }

    public function renderFooter(): void
    {
        echo $this->listRow($this->getTimestampItems());
    }
}