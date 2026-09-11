<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Migrations;

use Hirtz\Cms\Hotspot\Migrations\M260912120000Assets;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Test\Fixtures\AssetFixture;
use Hirtz\Media\Models\Asset;
use Hirtz\Media\Models\File;
use Hirtz\Skeleton\Models\Trail;
use Override;
use Yii;
use yii\db\Expression;
use yii\db\JsonExpression;

/**
 * The hotspot copy shifts every id past the cms ones, in the rows and in both places the trail stores them. Nothing
 * marks a row as shifted, so this is the part that must be right the first time.
 *
 * The DDL below commits the test transaction, so everything is cleaned up by hand.
 */
class AssetMigrationTest extends TestCase
{
    use HotspotFixtureTrait {
        fixtures as hotspotFixtures;
    }

    private const string LEGACY_FILE_COUNT_COLUMN = 'hotspot_asset_count';
    private const int LEGACY_ASSET_ID = 1;

    /**
     * The cms asset the fixture's hotspot sits on; `cms_asset` still carries its id after the cms copy.
     */
    private const int CMS_ASSET_ID = 4;

    /**
     * The migration asserts that `asset` holds exactly the hotspot assets it copied, so the fixture contributes the
     * cms rows only — which is also what gives the offset something to clear.
     */
    #[Override]
    public function fixtures(): array
    {
        return [
            ...$this->hotspotFixtures(),
            'asset' => AssetFixture::class,
        ];
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $db = Yii::$app->getDb();

        $db->createCommand()->dropColumn(Asset::tableName(), 'hotspot_count')->execute();
        $db->createCommand()
            ->addColumn(File::tableName(), self::LEGACY_FILE_COUNT_COLUMN, 'smallint NOT NULL DEFAULT 0')
            ->execute();
    }

    #[Override]
    protected function tearDown(): void
    {
        $db = Yii::$app->getDb();

        if ($db->getTableSchema(File::tableName(), true)->getColumn(self::LEGACY_FILE_COUNT_COLUMN)) {
            $db->createCommand()->dropColumn(File::tableName(), self::LEGACY_FILE_COUNT_COLUMN)->execute();
        }

        if (!$db->getTableSchema(Asset::tableName(), true)->getColumn('hotspot_count')) {
            $db->createCommand()
                ->addColumn(Asset::tableName(), 'hotspot_count', 'smallint NOT NULL DEFAULT 0 AFTER `position`')
                ->execute();
        }

        $db->createCommand()->delete('{{%hotspot_asset}}')->execute();
        $db->createCommand()->delete('{{%cms_asset}}')->execute();
        Trail::deleteAll(['model_class' => [HotspotAsset::class, Hotspot::class]]);

        parent::tearDown();
    }

    public function testTheCopiedAssetIsShiftedPastTheCmsIds(): void
    {
        $offset = $this->migrate();

        self::assertSame(7, $offset, 'the cms fixture occupies ids 1 to 7');

        $asset = Asset::findOne($offset + self::LEGACY_ASSET_ID);

        self::assertInstanceOf(HotspotAsset::class, $asset);
        self::assertSame(Hotspot::class, $asset->model_class);
        self::assertSame(1, $asset->model_id);
        self::assertSame(['name' => 'Hotspot asset'], $asset->getAttribute('custom_attributes'));
    }

    public function testTheTrailIdsAreShifted(): void
    {
        $offset = $this->migrate();

        $trail = Trail::find()
            ->where(['model_class' => HotspotAsset::class])
            ->one();

        self::assertSame((string)($offset + self::LEGACY_ASSET_ID), $trail->model_id);
    }

    public function testAChildTrailRowStillResolvesItsAsset(): void
    {
        $offset = $this->migrate();

        $trail = Trail::find()
            ->where(['model_class' => Hotspot::class, 'type' => Trail::TYPE_CHILD_UPDATE])
            ->one();

        self::assertSame($offset + self::LEGACY_ASSET_ID, (int)$trail->data['model_id']);
        self::assertInstanceOf(HotspotAsset::class, $trail->getDataModelRecord());
    }

    public function testTheHotspotCountsAreCarriedOver(): void
    {
        $this->migrate();

        self::assertSame(1, Asset::findOne(self::CMS_ASSET_ID)->getAttribute('hotspot_count'));
    }

    /**
     * @return int the offset the migration used
     */
    protected function migrate(): int
    {
        $this->seed();
        $offset = (int)Asset::find()->max('[[id]]');

        ob_start();
        (new M260912120000Assets())->up();
        ob_end_clean();

        return $offset;
    }

    protected function seed(): void
    {
        $db = Yii::$app->getDb();
        $now = new Expression('UTC_TIMESTAMP()');

        $db->createCommand()->insert('{{%hotspot_asset}}', [
            'id' => self::LEGACY_ASSET_ID,
            'status' => Asset::STATUS_ENABLED,
            'type' => Asset::TYPE_DEFAULT,
            'hotspot_id' => 1,
            'file_id' => 1,
            'position' => 1,
            'name' => 'Hotspot asset',
            'created_at' => $now,
        ])->execute();

        // The counts are carried over from `cms_asset`, which the cms migration left holding the same ids.
        $db->createCommand()->insert('{{%cms_asset}}', [
            'id' => self::CMS_ASSET_ID,
            'status' => Asset::STATUS_ENABLED,
            'type' => Asset::TYPE_DEFAULT,
            'entry_id' => 1,
            'section_id' => 1,
            'file_id' => 3,
            'position' => 1,
            'hotspot_count' => 1,
            'created_at' => $now,
        ])->execute();

        $files = $db->quoteTableName($db->getSchema()->getRawTableName(File::tableName()));
        $legacy = $db->quoteTableName($db->getSchema()->getRawTableName('{{%hotspot_asset}}'));
        $column = $db->quoteColumnName(self::LEGACY_FILE_COUNT_COLUMN);

        $db->createCommand("
            UPDATE $files f SET f.$column = (SELECT COUNT(*) FROM $legacy h WHERE h.file_id = f.id)
        ")->execute();

        // `batchInsert()` does not encode an array, and re-encodes a string, so the values are wrapped by hand.
        $db->createCommand()->batchInsert(Trail::tableName(), ['type', 'model_class', 'model_id', 'data', 'created_at'], [
            [Trail::TYPE_CREATE, HotspotAsset::class, (string)self::LEGACY_ASSET_ID,
                new JsonExpression(['name' => 'Hotspot asset']), $now],
            [Trail::TYPE_CHILD_UPDATE, Hotspot::class, '1',
                new JsonExpression([
                    'model_class' => HotspotAsset::class,
                    'model_id' => (string)self::LEGACY_ASSET_ID,
                ]), $now],
        ])->execute();
    }
}
