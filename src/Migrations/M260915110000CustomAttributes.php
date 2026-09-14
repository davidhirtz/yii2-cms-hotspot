<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Migrations;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260915110000CustomAttributes extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->moveColumnsToCustomAttributes(Hotspot::tableName(), ['name', 'content', 'link'], Hotspot::class);
    }

    public function safeDown(): void
    {
        $this->restoreColumnsFromCustomAttributes(Hotspot::tableName(), [
            'name' => (string)$this->string()->null()->after('asset_id'),
            'content' => (string)$this->text()->null()->after('name'),
            'link' => (string)$this->string(250)->null()->after('content'),
        ], Hotspot::class);
    }
}
