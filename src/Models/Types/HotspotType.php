<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\Types;

use Hirtz\Media\Models\Interfaces\AssetModelTypeInterface;
use Hirtz\Media\Models\Types\Traits\AssetModelTypeTrait;
use Hirtz\Skeleton\Models\Types\Type;

class HotspotType extends Type implements AssetModelTypeInterface
{
    use AssetModelTypeTrait;
}
