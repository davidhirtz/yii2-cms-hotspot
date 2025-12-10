<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\Modules\Admin\Widgets\Forms;

use Hirtz\Cms\hotspot\Models\Hotspot;
use Hirtz\Cms\modules\ModuleTrait;
use Hirtz\Skeleton\Modules\Admin\Widgets\Forms\Traits\ContentFieldTrait;
use Hirtz\Skeleton\Modules\Admin\Widgets\Forms\Traits\ModelTimestampTrait;
use Hirtz\Skeleton\Modules\Admin\Widgets\Forms\Traits\StatusFieldTrait;
use Hirtz\Skeleton\Modules\Admin\Widgets\Forms\Traits\TypeFieldTrait;
use Hirtz\Skeleton\Widgets\Bootstrap\ActiveField;
use Hirtz\Skeleton\Widgets\Bootstrap\ActiveForm;

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
