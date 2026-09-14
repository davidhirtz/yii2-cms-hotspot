<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotController;
use Hirtz\Skeleton\Widgets\Buttons\DeleteButton;
use Hirtz\Skeleton\Widgets\Buttons\DuplicateButton;
use Hirtz\Skeleton\Widgets\Navs\ActionDropdown;
use Hirtz\Skeleton\Widgets\Traits\ModelTrait;
use Override;
use Stringable;
use Yii;

class HotspotActionDropdown extends ActionDropdown
{
    /**
     * @use ModelTrait<Hotspot>
     */
    use ModelTrait;

    #[Override]
    protected function configure(): void
    {
        $this->addItem(
            $this->getDuplicateButton(),
            $this->getDeleteButton(),
        );

        parent::configure();
    }

    /**
     * @see HotspotController::actionDuplicate()
     */
    protected function getDuplicateButton(): ?Stringable
    {
        return DuplicateButton::make()
            ->model($this->model)
            ->url(['/admin/hotspot/hotspot/duplicate', 'id' => $this->model->id]);
    }

    /**
     * @see HotspotController::actionDelete()
     */
    protected function getDeleteButton(): ?Stringable
    {
        return DeleteButton::make()
            ->model($this->model)
            ->label(Yii::t('hotspot', 'HOTSPOT_DELETE_TITLE'))
            ->url(['/admin/hotspot/hotspot/delete', 'id' => $this->model->id]);
    }
}
