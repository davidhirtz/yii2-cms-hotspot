<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\tests\fixtures;

use Hirtz\Skeleton\models\User;
use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = User::class;
}
