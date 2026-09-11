<?php

declare(strict_types=1);

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Media\Models\Asset;
use yii\db\Expression;

return [
    ...require(Yii::getAlias('@cms') . '/Test/Fixtures/Data/asset.php'),
    'hotspot-asset-1-1' => [
        'id' => 8,
        'status' => Asset::STATUS_ENABLED,
        'type' => Asset::TYPE_DEFAULT,
        'model_class' => Hotspot::class,
        'model_id' => 1,
        'file_id' => 1,
        'position' => 1,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];
