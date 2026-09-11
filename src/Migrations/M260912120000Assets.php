<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Migrations;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Media\Models\Asset;
use Hirtz\Media\Models\File;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\Trail;
use Hirtz\Skeleton\Models\Translation;
use RuntimeException;
use Yii;
use yii\db\Migration;
use yii\db\Query;

/**
 * Copies `hotspot_asset` into the polymorphic `asset` table, shifting the ids past the cms ones. The id shift must
 * run exactly once: nothing in a row marks it as shifted, so never re-run this outside the migration history.
 *
 * @noinspection PhpUnused
 */
class M260912120000Assets extends Migration
{
    use MigrationTrait;

    private const string LEGACY_TABLE = '{{%hotspot_asset}}';
    private const string LEGACY_CMS_ASSET_TABLE = '{{%cms_asset}}';

    /**
     * @var list<string> the columns that become custom attributes; a hotspot asset has no `embed_url`
     */
    private array $textColumns = ['name', 'content', 'alt_text', 'link'];

    private int $offset = 0;

    public function safeUp(): void
    {
        if (!$this->getDb()->getTableSchema(self::LEGACY_TABLE, true)) {
            echo "    > hotspot_asset is gone, nothing to copy\n";
            return;
        }

        $this->offset = (int)(new Query())->from(Asset::tableName())->max('[[id]]');
        echo "    > shifting hotspot asset ids by $this->offset\n";

        $this->repointHotspotForeignKey();
        $this->copyHotspotCounts();
        $this->copyAssets();
        $this->copyTranslatedColumns();
        $this->copyTranslations();
        $this->shiftTrailIds();
        $this->foldFileCounts();
        $this->assert();
    }

    public function safeDown(): bool
    {
        echo "    > the asset copy cannot be reverted, restore a dump instead\n";
        return false;
    }

    protected function repointHotspotForeignKey(): void
    {
        $name = $this->getForeignKeyName(Hotspot::tableName(), 'asset_id') . '_ibfk';

        $this->dropForeignKey($name, Hotspot::tableName());
        $this->addForeignKey($name, Hotspot::tableName(), 'asset_id', Asset::tableName(), 'id', 'CASCADE');
    }

    protected function copyHotspotCounts(): void
    {
        $this->addColumn(Asset::tableName(), 'hotspot_count', (string)$this->smallInteger()
            ->notNull()
            ->defaultValue(0)
            ->after('position'));

        $assets = $this->getQuotedTableName(Asset::tableName());
        $legacy = $this->getQuotedTableName(self::LEGACY_CMS_ASSET_TABLE);

        $this->execute("
            UPDATE $assets [[a]] JOIN $legacy [[c]] ON [[c]].[[id]] = [[a]].[[id]]
            SET [[a]].[[hotspot_count]] = [[c]].[[hotspot_count]]
        ");
    }

    protected function copyAssets(): void
    {
        $db = $this->getDb();
        $legacy = $this->getQuotedTableName(self::LEGACY_TABLE);
        $assets = $this->getQuotedTableName(Asset::tableName());
        $hotspot = $db->quoteValue(Hotspot::class);

        $json = [];

        foreach ($this->textColumns as $column) {
            $json[] = $db->quoteValue($column) . ", NULLIF([[$column]], '')";
        }

        $json = implode(', ', $json);

        $this->execute("
            INSERT INTO $assets ([[id]], [[status]], [[type]], [[model_class]], [[model_id]], [[file_id]],
                [[position]], [[custom_attributes]], [[updated_by_user_id]], [[updated_at]], [[created_at]])
            SELECT [[id]] + $this->offset, [[status]], [[type]], $hotspot, [[hotspot_id]], [[file_id]], [[position]],
                JSON_MERGE_PATCH(COALESCE([[custom_attributes]], '{}'), JSON_OBJECT($json)),
                [[updated_by_user_id]], [[updated_at]], [[created_at]]
            FROM $legacy
        ");

        $count = (new Query())->from(self::LEGACY_TABLE)->count();
        echo "    > copied $count hotspot assets\n";
    }

    protected function copyTranslatedColumns(): void
    {
        $columns = $this->getDb()->getTableSchema(self::LEGACY_TABLE, true)->getColumnNames();
        $assets = $this->getQuotedTableName(Asset::tableName());
        $legacy = $this->getQuotedTableName(self::LEGACY_TABLE);
        $copied = [];

        foreach ($columns as $column) {
            foreach ($this->textColumns as $attribute) {
                if ($column !== $attribute && str_starts_with($column, $attribute . '_')) {
                    $copied[] = $column;
                }
            }
        }

        foreach ($copied as $column) {
            $this->execute("
                UPDATE $assets [[a]] JOIN $legacy [[c]] ON [[c]].[[id]] + $this->offset = [[a]].[[id]]
                SET [[a]].[[custom_attributes]] = JSON_SET(COALESCE([[a]].[[custom_attributes]], '{}'),
                    {$this->getDb()->quoteValue('$."' . $column . '"')}, [[c]].[[$column]])
                WHERE [[c]].[[$column]] IS NOT NULL AND [[c]].[[$column]] != ''
            ");
        }

        $count = count($copied);
        echo "    > copied $count translated columns\n";
    }

    protected function copyTranslations(): void
    {
        $i18n = Yii::$app->getI18n();
        $count = 0;

        $query = (new Query())
            ->select(['model_id', 'language', 'attribute', 'value'])
            ->from(Translation::tableName())
            ->where(['model_class' => HotspotAsset::class]);

        foreach ($query->each() as $row) {
            if (!in_array($row['attribute'], $this->textColumns, true)) {
                continue;
            }

            $name = $i18n->getAttributeName($row['attribute'], $row['language']);

            $this->execute('
                UPDATE ' . $this->getQuotedTableName(Asset::tableName()) . '
                SET [[custom_attributes]] = JSON_SET(COALESCE([[custom_attributes]], \'{}\'), :path, :value)
                WHERE [[id]] = :id
            ', [
                ':path' => '$."' . $name . '"',
                ':value' => $row['value'],
                ':id' => (int)$row['model_id'] + $this->offset,
            ]);

            $count++;
        }

        echo "    > copied $count translations\n";
    }

    /**
     * The subclass keeps the name the old model had, so only the id moves — in both places it is stored.
     */
    protected function shiftTrailIds(): void
    {
        $db = $this->getDb();
        $trail = $this->getQuotedTableName(Trail::tableName());
        $class = $db->quoteValue(HotspotAsset::class);

        $expected = (int)(new Query())
            ->from(Trail::tableName())
            ->where(['model_class' => HotspotAsset::class])
            ->count();

        $rows = $db->createCommand("
            UPDATE $trail SET [[model_id]] = CAST([[model_id]] AS UNSIGNED) + $this->offset
            WHERE [[model_class]] = $class
        ")->execute();

        if ($rows !== $expected) {
            throw new RuntimeException("Shifted $rows trail rows but expected $expected.");
        }

        $expected = (int)$db->createCommand("
            SELECT COUNT(*) FROM $trail
            WHERE JSON_UNQUOTE(JSON_EXTRACT([[data]], '$.model_class')) = $class
        ")->queryScalar();

        $rows = $db->createCommand("
            UPDATE $trail
            SET [[data]] = JSON_SET([[data]], '$.model_id',
                CAST(JSON_UNQUOTE(JSON_EXTRACT([[data]], '$.model_id')) AS UNSIGNED) + $this->offset)
            WHERE JSON_UNQUOTE(JSON_EXTRACT([[data]], '$.model_class')) = $class
        ")->execute();

        if ($rows !== $expected) {
            throw new RuntimeException("Shifted $rows child trail rows but expected $expected.");
        }

        echo "    > shifted the ids of $expected child trail rows\n";
    }

    protected function foldFileCounts(): void
    {
        $files = $this->getQuotedTableName(File::tableName());

        $this->execute("UPDATE $files SET [[asset_count]] = [[asset_count]] + [[hotspot_asset_count]]");

        $this->dropIndexesContainingColumn(File::tableName(), 'hotspot_asset_count');
        $this->dropColumn(File::tableName(), 'hotspot_asset_count');
    }

    protected function assert(): void
    {
        $db = $this->getDb();
        $legacy = $this->getQuotedTableName(self::LEGACY_TABLE);
        $assets = $this->getQuotedTableName(Asset::tableName());
        $trail = $this->getQuotedTableName(Trail::tableName());
        $files = $this->getQuotedTableName(File::tableName());

        $hotspot = $db->quoteValue(Hotspot::class);
        $class = $db->quoteValue(HotspotAsset::class);

        $checks = [
            'row count' => "
                SELECT (SELECT COUNT(*) FROM $legacy)
                     - (SELECT COUNT(*) FROM $assets WHERE [[model_class]] = $hotspot)",
            'scalar columns' => "
                SELECT COUNT(*) FROM $legacy [[c]]
                LEFT JOIN $assets [[a]] ON [[a]].[[id]] = [[c]].[[id]] + $this->offset
                WHERE [[a]].[[id]] IS NULL OR [[a]].[[status]] <> [[c]].[[status]]
                   OR [[a]].[[type]] <> [[c]].[[type]] OR [[a]].[[file_id]] <> [[c]].[[file_id]]
                   OR [[a]].[[position]] <> [[c]].[[position]] OR [[a]].[[model_id]] <> [[c]].[[hotspot_id]]
                   OR [[a]].[[model_class]] <> $hotspot
                   OR NOT ([[a]].[[updated_by_user_id]] <=> [[c]].[[updated_by_user_id]])
                   OR NOT ([[a]].[[updated_at]] <=> [[c]].[[updated_at]])
                   OR [[a]].[[created_at]] <> [[c]].[[created_at]]",
            'unshifted trail ids' => "
                SELECT COUNT(*) FROM $trail
                WHERE [[model_class]] = $class AND CAST([[model_id]] AS UNSIGNED) <= $this->offset",
            'unshifted child trail ids' => "
                SELECT COUNT(*) FROM $trail
                WHERE JSON_UNQUOTE(JSON_EXTRACT([[data]], '$.model_class')) = $class
                  AND CAST(JSON_UNQUOTE(JSON_EXTRACT([[data]], '$.model_id')) AS UNSIGNED) <= $this->offset",
            'file counts' => "
                SELECT COUNT(*) FROM $files [[f]]
                WHERE [[f]].[[asset_count]] <> (SELECT COUNT(*) FROM $assets [[a]] WHERE [[a]].[[file_id]] = [[f]].[[id]])",
        ];

        foreach ($this->textColumns as $column) {
            $name = $db->quoteValue('$."' . $column . '"');

            $checks["column $column"] = "
                SELECT COUNT(*) FROM $legacy [[c]]
                JOIN $assets [[a]] ON [[a]].[[id]] = [[c]].[[id]] + $this->offset
                WHERE NOT (JSON_UNQUOTE(JSON_EXTRACT([[a]].[[custom_attributes]], $name))
                    <=> NULLIF([[c]].[[$column]], ''))";
        }

        foreach ($checks as $name => $sql) {
            $count = (int)$db->createCommand($sql)->queryScalar();

            if ($count !== 0) {
                throw new RuntimeException("Hotspot asset migration check \"$name\" failed with $count rows.");
            }
        }

        echo '    > ' . count($checks) . " assertions passed\n";
    }
}
