<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\Modules\Admin\Widgets\Panels;

use Hirtz\Cms\hotspot\Models\Hotspot;
use Hirtz\Cms\hotspot\Modules\Admin\Controllers\HotspotController;
use Hirtz\Media\Modules\Admin\Widgets\Forms\Traits\DuplicateButtonTrait;
use Hirtz\Skeleton\Modules\Admin\Widgets\Panels\HelpPanel;

class HotspotHelpPanel extends HelpPanel
{
    use DuplicateButtonTrait;

    public ?Hotspot $model = null;

    public function init(): void
    {
        $this->content ??= $this->renderButtonToolbar($this->getButtons());
        parent::init();
    }

    /**
     * @see HotspotController::actionDuplicate()
     */
    protected function getButtons(): array
    {
        return array_filter([
            $this->getDuplicateButton(),
        ]);
    }
}
