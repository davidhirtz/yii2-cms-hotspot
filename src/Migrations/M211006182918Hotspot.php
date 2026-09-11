<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Migrations;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Media\Models\File;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\User;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

final class M211006182918Hotspot extends Migration
{
    use MigrationTrait;

    private const string LEGACY_TABLE = '{{%hotspot_asset}}';
    private const string LEGACY_CMS_ASSET_TABLE = '{{%cms_asset}}';
    private const string FILE_COUNT_COLUMN = 'hotspot_asset_count';

    public function safeUp(): void
    {
        $this->createTable(Hotspot::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(Hotspot::STATUS_ENABLED),
            'type' => $this->smallInteger()->unsigned()->notNull()->defaultValue(Hotspot::TYPE_DEFAULT),
            'asset_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string()->null(),
            'content' => $this->text()->null(),
            'link' => $this->string()->null(),
            'x' => $this->decimal(5, 2)->notNull(),
            'y' => $this->decimal(5, 2)->notNull(),
            'position' => $this->smallInteger()->notNull()->defaultValue(0),
            'asset_count' => $this->smallInteger()->notNull()->defaultValue(0),
            'updated_by_user_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('asset_id', Hotspot::tableName(), ['asset_id', 'position']);

        $this->addForeignKey(
            $this->getForeignKeyName(Hotspot::tableName(), 'asset_id_ibfk'),
            Hotspot::tableName(),
            'asset_id',
            self::LEGACY_CMS_ASSET_TABLE,
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->getForeignKeyName(Hotspot::tableName(), 'updated_by_ibfk'),
            Hotspot::tableName(),
            'updated_by_user_id',
            User::tableName(),
            'id',
            'SET NULL'
        );

        $this->addColumn(self::LEGACY_CMS_ASSET_TABLE, 'hotspot_count', (string)$this->smallInteger()
            ->notNull()
            ->defaultValue(0)
            ->after('link'));

        $this->createTable(self::LEGACY_TABLE, [
            'id' => $this->primaryKey()->unsigned(),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(HotspotAsset::STATUS_ENABLED),
            'type' => $this->smallInteger()->notNull()->defaultValue(HotspotAsset::TYPE_DEFAULT),
            'hotspot_id' => $this->integer()->unsigned()->notNull(),
            'file_id' => $this->integer()->unsigned()->notNull(),
            'position' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'name' => $this->string(250)->null(),
            'content' => $this->text()->null(),
            'alt_text' => $this->string(250)->null(),
            'link' => $this->string(250)->null(),
            'updated_by_user_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull(),
        ], $this->getTableOptions());

        $this->createIndex('hotspot_id', self::LEGACY_TABLE, ['hotspot_id', 'position']);

        $this->addForeignKey(
            $this->getForeignKeyName(self::LEGACY_TABLE, 'hotspot_id_ibfk'),
            self::LEGACY_TABLE,
            'hotspot_id',
            Hotspot::tableName(),
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->getForeignKeyName(self::LEGACY_TABLE, 'file_id_ibfk'),
            self::LEGACY_TABLE,
            'file_id',
            File::tableName(),
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->getForeignKeyName(self::LEGACY_TABLE, 'updated_by_ibfk'),
            self::LEGACY_TABLE,
            'updated_by_user_id',
            User::tableName(),
            'id',
            'SET NULL'
        );

        $this->addColumn(File::tableName(), self::FILE_COUNT_COLUMN, (string)$this->smallInteger()
            ->notNull()
            ->defaultValue(0)
            ->after('transformation_count'));
    }

    public function safeDown(): void
    {
        $this->dropColumn(File::tableName(), self::FILE_COUNT_COLUMN);

        $this->dropColumn(self::LEGACY_CMS_ASSET_TABLE, 'hotspot_count');

        $this->dropTable(self::LEGACY_TABLE);
        $this->dropTable(Hotspot::tableName());
    }
}
