<?php

namespace davidhirtz\yii2\annotation\migrations;

use davidhirtz\yii2\annotation\models\Annotation;
use davidhirtz\yii2\annotation\models\AnnotationAsset;
use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\skeleton\db\MigrationTrait;
use davidhirtz\yii2\skeleton\models\User;
use Yii;
use yii\db\Migration;

/**
* Class M211006182918Annotation
* @package davidhirtz\yii2\annotation\migrations
* @noinspection PhpUnused
*/
class M211006182918Annotation extends Migration
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

            $this->createTable(Annotation::tableName(), [
                'id' => $this->primaryKey()->unsigned(),
                'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(Annotation::STATUS_ENABLED),
                'type' => $this->smallInteger()->unsigned()->notNull()->defaultValue(Annotation::TYPE_DEFAULT),
                'asset_id' => $this->integer()->unsigned()->notNull(),
                'name' => $this->string()->null(),
                'content' => $this->text()->null(),
                'link' => $this->string()->null(),
                'x' => $this->decimal(5.2)->notNull(),
                'y' => $this->decimal(5.2)->notNull(),
                'position' => $this->smallInteger()->notNull()->defaultValue(0),
                'asset_count' => $this->smallInteger()->notNull()->defaultValue(0),
                'updated_by_user_id' => $this->integer()->unsigned()->null(),
                'updated_at' => $this->dateTime(),
                'created_at' => $this->dateTime()->notNull(),
            ]);

            $this->addI18nColumns(Annotation::tableName(), Annotation::instance()->i18nAttributes);
            
            $this->createIndex('asset_id', Annotation::tableName(), ['asset_id', 'position']);
            
            $tableName = $schema->getRawTableName(Annotation::tableName());
            $this->addForeignKey($tableName . '_asset_id_ibfk', Annotation::tableName(), 'asset_id', Asset::tableName(), 'id', 'CASCADE');
            $this->addForeignKey($tableName . '_updated_by_ibfk', Annotation::tableName(), 'updated_by_user_id', User::tableName(), 'id', 'SET NULL');

            $this->addColumn(Asset::tableName(), Asset::instance()->getI18nAttributeName('annotation_count', $language), $this->smallInteger()->notNull()->defaultValue(0)->after('link'));

            $this->createTable(AnnotationAsset::tableName(), [
                'id' => $this->primaryKey()->unsigned(),
                'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(AnnotationAsset::STATUS_ENABLED),
                'type' => $this->smallInteger()->notNull()->defaultValue(AnnotationAsset::TYPE_DEFAULT),
                'annotation_id' => $this->integer()->unsigned()->notNull(),
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

            $this->addI18nColumns(AnnotationAsset::tableName(), AnnotationAsset::instance()->i18nAttributes);

            $this->createIndex('annotation_id', AnnotationAsset::tableName(), ['annotation_id', 'position']);

            $tableName = $schema->getRawTableName(AnnotationAsset::tableName());
            $this->addForeignKey($tableName . '_annotation_id_ibfk', AnnotationAsset::tableName(), 'annotation_id', Annotation::tableName(), 'id', 'CASCADE');
            $this->addForeignKey($tableName . '_file_id_ibfk', AnnotationAsset::tableName(), 'file_id', File::tableName(), 'id', 'CASCADE');
            $this->addForeignKey($tableName . '_updated_by_ibfk', AnnotationAsset::tableName(), 'updated_by_user_id', User::tableName(), 'id', 'SET NULL');

            $this->addColumn(File::tableName(), File::instance()->getI18nAttributeName('annotation_asset_count', $language), $this->smallInteger()->notNull()->defaultValue(0)->after('cms_asset_count'));
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

            $this->dropColumn(File::tableName(), $i18n->getAttributeName('annotation_asset_count', $language));

            $this->dropTable(AnnotationAsset::tableName());
            $this->dropTable(Annotation::tableName());
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