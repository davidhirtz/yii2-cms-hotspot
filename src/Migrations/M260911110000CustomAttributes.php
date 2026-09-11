<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Migrations;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260911110000CustomAttributes extends Migration
{
    use MigrationTrait;

    private const string LEGACY_TABLE = '{{%hotspot_asset}}';

    public function safeUp(): void
    {
        foreach ($this->getTableNames() as $table) {
            $this->addCustomAttributesColumn($table);
        }
    }

    public function safeDown(): void
    {
        foreach ($this->getTableNames() as $table) {
            $this->dropCustomAttributesColumn($table);
        }
    }

    /**
     * @return list<string>
     */
    protected function getTableNames(): array
    {
        return [
            Hotspot::tableName(),
            self::LEGACY_TABLE,
        ];
    }
}
