<?php

declare(strict_types=1);

use Hirtz\Cms\Hotspot\Models\Hotspot;
use yii\db\Expression;

return [
    'hotspot-1' => [
        'id' => 1,
        'status' => Hotspot::STATUS_ENABLED,
        'asset_id' => 4,
        'name' => 'Test Hotspot 1',
        'content' => 'Test content for hotspot 1.',
        'link' => 'https://example.com',
        'x' => 10.00,
        'y' => 20.00,
        'asset_count' => 1,
        'position' => 1,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'hotspot-2' => [
        'id' => 2,
        'status' => Hotspot::STATUS_ENABLED,
        'asset_id' => 4,
        'name' => 'Test Hotspot 2',
        'content' => 'Test content for hotspot 2',
        'x' => 20.00,
        'y' => 50.00,
        'position' => 2,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];
