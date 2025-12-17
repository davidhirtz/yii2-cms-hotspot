<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\events;

use Hirtz\Cms\Models\Asset;
use Hirtz\Skeleton\Models\Events\DuplicateActiveRecordEvent;

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
