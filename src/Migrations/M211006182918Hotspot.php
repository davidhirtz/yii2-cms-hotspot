<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Migrations;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Migrations\Traits\I18nTablesTrait;
use Hirtz\Cms\Models\Asset;
use Hirtz\Media\Models\File;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\User;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M211006182918Hotspot extends Migration
{
    use MigrationTrait;
    use I18nTablesTrait;

    public function safeUp(): void
    {
        $this->i18nTablesCallback(function (): void {
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

            $this->addI18nColumns(Hotspot::tableName(), Hotspot::instance()->i18nAttributes);

            $this->createIndex('asset_id', Hotspot::tableName(), ['asset_id', 'position']);

            $this->addForeignKey(
                $this->getForeignKeyName(Hotspot::tableName(), 'asset_id_ibfk'),
                Hotspot::tableName(),
                'asset_id',
                Asset::tableName(),
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

            $this->addColumn(Asset::tableName(), 'hotspot_count', (string)$this->smallInteger()
                ->notNull()
                ->defaultValue(0)
                ->after('link'));

            $this->createTable(HotspotAsset::tableName(), [
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

            $this->addI18nColumns(HotspotAsset::tableName(), HotspotAsset::instance()->i18nAttributes);

            $this->createIndex('hotspot_id', HotspotAsset::tableName(), ['hotspot_id', 'position']);

            $this->addForeignKey(
                $this->getForeignKeyName(HotspotAsset::tableName(), 'hotspot_id_ibfk'),
                HotspotAsset::tableName(),
                'hotspot_id',
                Hotspot::tableName(),
                'id',
                'CASCADE'
            );

            $this->addForeignKey(
                $this->getForeignKeyName(HotspotAsset::tableName(), 'file_id_ibfk'),
                HotspotAsset::tableName(),
                'file_id',
                File::tableName(),
                'id',
                'CASCADE'
            );

            $this->addForeignKey(
                $this->getForeignKeyName(HotspotAsset::tableName(), 'updated_by_ibfk'),
                HotspotAsset::tableName(),
                'updated_by_user_id',
                User::tableName(),
                'id',
                'SET NULL'
            );
        });

        $after = 'transformation_count';

        foreach (HotspotAsset::instance()->getFileCountAttributeNames() as $attributeName) {
            $this->addColumn(File::tableName(), $attributeName, (string)$this->smallInteger()
                ->notNull()
                ->defaultValue(0)
                ->after($after));

            $after = $attributeName;
        }
    }

    public function safeDown(): void
    {
        foreach (HotspotAsset::instance()->getFileCountAttributeNames() as $attributeName) {
            $this->dropColumn(File::tableName(), $attributeName);
        }

        $this->i18nTablesCallback(function (): void {
            $this->dropColumn(Asset::tableName(), 'hotspot_count');

            $this->dropTable(HotspotAsset::tableName());
            $this->dropTable(Hotspot::tableName());
        });
    }
}
