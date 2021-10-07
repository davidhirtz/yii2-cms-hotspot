<?php

namespace davidhirtz\yii2\hotspot\migrations;

use davidhirtz\yii2\hotspot\models\Hotspot;
use davidhirtz\yii2\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\skeleton\db\MigrationTrait;
use davidhirtz\yii2\skeleton\models\User;
use Yii;
use yii\db\Migration;

/**
* Class M211006182918Hotspot
* @package davidhirtz\yii2\hotspot\migrations
* @noinspection PhpUnused
*/
class M211006182918Hotspot extends Migration
{
    use MigrationTrait;
    use ModuleTrait;

    /**
     * @inheritDoc
     */
    public function safeUp()
    {
        $schema = $this->getDb()->getSchema();

        foreach ($this->getLanguages() as $language) {
            if ($language) {
                Yii::$app->language = $language;
            }

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

            $tableName = $schema->getRawTableName(Hotspot::tableName());
            $this->addForeignKey($tableName . '_asset_id_ibfk', Hotspot::tableName(), 'asset_id', Asset::tableName(), 'id', 'CASCADE');
            $this->addForeignKey($tableName . '_updated_by_ibfk', Hotspot::tableName(), 'updated_by_user_id', User::tableName(), 'id', 'SET NULL');

            $this->addColumn(Asset::tableName(), Asset::instance()->getI18nAttributeName('hotspot_count', $language), $this->smallInteger()->notNull()->defaultValue(0)->after('link'));

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

            $tableName = $schema->getRawTableName(HotspotAsset::tableName());
            $this->addForeignKey($tableName . '_hotspot_id_ibfk', HotspotAsset::tableName(), 'hotspot_id', Hotspot::tableName(), 'id', 'CASCADE');
            $this->addForeignKey($tableName . '_file_id_ibfk', HotspotAsset::tableName(), 'file_id', File::tableName(), 'id', 'CASCADE');
            $this->addForeignKey($tableName . '_updated_by_ibfk', HotspotAsset::tableName(), 'updated_by_user_id', User::tableName(), 'id', 'SET NULL');

            $this->addColumn(File::tableName(), File::instance()->getI18nAttributeName('hotspot_asset_count', $language), $this->smallInteger()->notNull()->defaultValue(0)->after('cms_asset_count'));
        }
    }

    /**
     * @inheritDoc
     */
    public function safeDown()
    {
        $i18n = Yii::$app->getI18n();

        foreach ($this->getLanguages() as $language) {
            Yii::$app->language = $language;

            $this->dropColumn(File::tableName(), $i18n->getAttributeName('hotspot_asset_count', $language));
            $this->dropColumn(Asset::tableName(), Asset::instance()->getI18nAttributeName('hotspot_count', $language));

            $this->dropTable(HotspotAsset::tableName());
            $this->dropTable(Hotspot::tableName());
        }
    }

    /**
     * @return array
     */
    private function getLanguages()
    {
        return static::getModule()->enableI18nTables ? Yii::$app->getI18n()->getLanguages() : [Yii::$app->language];
    }
}