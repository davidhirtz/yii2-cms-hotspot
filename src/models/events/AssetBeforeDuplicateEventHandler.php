<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\models\events;

use Hirtz\Cms\models\Asset;
use Hirtz\Skeleton\models\events\DuplicateActiveRecordEvent;

/**
 * @property DuplicateActiveRecordEvent $event
 */
class AssetBeforeDuplicateEventHandler
{
    public function __construct(
        protected readonly DuplicateActiveRecordEvent $event,
        protected readonly Asset $asset,
        protected readonly Asset $duplicate,
    ) {
        $this->handleEvent();
    }

    public function handleEvent(): void
    {
        $this->duplicate->setAttribute('hotspot_count', $this->asset->getAttribute('hotspot_count'));
    }
}
