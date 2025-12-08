<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\modules\admin\widgets\forms;

use Hirtz\Cms\hotspot\models\Hotspot;
use Hirtz\Cms\modules\ModuleTrait;
use Hirtz\Skeleton\modules\admin\widgets\forms\traits\ContentFieldTrait;
use Hirtz\Skeleton\modules\admin\widgets\forms\traits\ModelTimestampTrait;
use Hirtz\Skeleton\modules\admin\widgets\forms\traits\StatusFieldTrait;
use Hirtz\Skeleton\modules\admin\widgets\forms\traits\TypeFieldTrait;
use Hirtz\Skeleton\widgets\bootstrap\ActiveField;
use Hirtz\Skeleton\widgets\bootstrap\ActiveForm;

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
