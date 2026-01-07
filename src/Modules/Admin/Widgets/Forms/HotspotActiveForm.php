<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Modules\Admin\Widgets\Forms\Traits\ActiveFormFieldsTrait;
use Hirtz\Cms\Modules\ModuleTrait;
use Hirtz\Skeleton\Widgets\Forms\ActiveForm;
use Hirtz\Skeleton\Widgets\Forms\Fields\InputField;
use Override;
use Stringable;

/**
 * @property Hotspot $model
 */
class HotspotActiveForm extends ActiveForm
{
    use ActiveFormFieldsTrait;
    use ModuleTrait;

    #[Override]
    protected function configure(): void
    {
        $this->rows ??= [
            $this->getStatusField(),
            $this->getTypeField(),
            $this->getNameField(),
            $this->getContentField(),
            $this->getLinkField(),
            $this->getXField(),
            $this->getYField(),
        ];

        parent::configure();
    }

    protected function getXField(): ?Stringable
    {
        return $this->getCoordinateField('x');
    }

    protected function getYField(): ?Stringable
    {
        return $this->getCoordinateField('y');
    }

    protected function getCoordinateField(string $property): ?Stringable
    {
        return InputField::make()
            ->property($property)
            ->type('number')
            ->attribute('step', 0.01)
            ->append('%');
    }
}
