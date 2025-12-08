<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\modules\admin\widgets\panels;

use Hirtz\Cms\hotspot\models\Hotspot;
use Hirtz\Cms\hotspot\modules\admin\controllers\HotspotController;
use Hirtz\Media\modules\admin\widgets\panels\traits\DuplicateButtonTrait;
use Hirtz\Skeleton\modules\admin\widgets\panels\HelpPanel;

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
