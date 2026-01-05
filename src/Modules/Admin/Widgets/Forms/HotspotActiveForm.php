<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Modules\Admin\Widgets\Forms\Traits\ActiveFormFieldsTrait;
use Hirtz\Cms\Modules\ModuleTrait;
use Hirtz\Skeleton\Widgets\Forms\ActiveForm;
use Hirtz\Skeleton\Widgets\Forms\Fields\InputField;
use Stringable;

/**
 * @property Hotspot $model
 */
class HotspotActiveForm extends ActiveForm
{
    use ActiveFormFieldsTrait;
    use ModuleTrait;

    public function configure(): void
    {
        $this->rows ??= [
            $this->getStatusField(),
            $this->getTypeField(),
            $this->getNameField(),
            $this->getContentField(),
            $this->getLinkField(),
            $this->xField(),
            $this->yField(),
        ];

        parent::configure();
    }

    protected function getLinkField(): ?Stringable
    {
        return InputField::make()
            ->property('link');
    }

    public function xField(): ?Stringable
    {
        return $this->getCoordinateField('x');
    }

    public function yField(): ?Stringable
    {
        return $this->getCoordinateField('y');
    }

    protected function getCoordinateField(string $property): ?Stringable
    {
        return InputField::make()
            ->property($property)
            ->append('%');
    }
}
