<?php

declare(strict_types=1);

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use yii\db\Expression;

return [
    'hotspot-asset-1-1' => [
        'id' => 1,
        'status' => HotspotAsset::STATUS_DEFAULT,
        'hotspot_id' => 1,
        'file_id' => 1,
        'position' => 1,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];
