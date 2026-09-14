<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Migrations;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260915160000CustomAttributesColumn extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->moveCustomAttributesColumn(Hotspot::tableName(), 'y');
    }

    public function safeDown(): void
    {
        $this->moveCustomAttributesColumnToEnd(Hotspot::tableName());
    }
}
