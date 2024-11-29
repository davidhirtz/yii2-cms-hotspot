<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\forms;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ContentFieldTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ModelTimestampTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\StatusFieldTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\TypeFieldTrait;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveField;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveForm;

/**
 * @property Hotspot $model
 */
class HotspotActiveForm extends ActiveForm
{
    use ContentFieldTrait;
    use ModuleTrait;
    use ModelTimestampTrait;
    use StatusFieldTrait;
    use TypeFieldTrait;

    public bool $hasStickyButtons = true;

    /**
     * @uses static::statusField()
     * @uses static::typeField()
     * @uses static::contentField()
     * @uses static::xField()
     * @uses static::yField()
     */
    public function init(): void
    {
        $this->fields ??= [
            'status',
            'type',
            'name',
            'content',
            'link',
            'x',
            'y',
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
}
