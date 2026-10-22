<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models\Events;

use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Media\Models\File;
use Hirtz\Skeleton\Helpers\FileHelper;
use Yii;
use yii\db\Query;

/**
 * Hotspots are placed on the image, so neither path to another file leaves them over a video or a PDF.
 */
class FileWithoutPreviewTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testAnAssetWithHotspotsRefusesAFileWithoutAnImage(): void
    {
        $asset = SectionAsset::findOne(4) ?? self::fail('The fixture asset is missing.');
        self::assertSame(2, $asset->getAttribute('hotspot_count'));

        $file = $this->findOtherFile($asset);
        $file->updateAttributes(['extension' => 'pdf']);

        $asset->populateFileRelation($file);

        self::assertFalse($asset->update());
        self::assertSame(Yii::t('hotspot', 'HOTSPOT_ERROR_FILE_WITHOUT_PREVIEW'), $asset->getFirstError('file_id'));
    }

    public function testAnAssetWithHotspotsTakesAnotherImage(): void
    {
        $asset = SectionAsset::findOne(4) ?? self::fail('The fixture asset is missing.');
        $file = $this->findOtherFile($asset);

        self::assertTrue($file->hasPreview());

        $asset->populateFileRelation($file);

        self::assertSame(1, $asset->update(), print_r($asset->getErrors(), true));
    }

    public function testTheFileOfAnAssetWithHotspotsRefusesAnUploadWithoutAnImage(): void
    {
        $asset = SectionAsset::findOne(4) ?? self::fail('The fixture asset is missing.');
        $file = File::findOne($asset->file_id) ?? self::fail('The fixture file is missing.');

        $directory = Yii::getAlias('@runtime/hotspot-preview');
        FileHelper::createDirectory($directory);
        file_put_contents("$directory/notes.txt", 'Å 🙂');

        try {
            $file->allowedExtensions = ['jpg', 'txt'];
            $file->checkExtensionByMimeType = false;

            self::assertTrue($file->copy("$directory/notes.txt"));
            self::assertFalse($file->update());
            self::assertSame(Yii::t('hotspot', 'HOTSPOT_ERROR_FILE_WITHOUT_PREVIEW'), $file->getFirstError('upload'));
        } finally {
            FileHelper::removeDirectory($directory);
        }
    }

    /**
     * A copy of the asset's file row: a new record of its own would need an upload.
     */
    private function findOtherFile(SectionAsset $asset): File
    {
        $row = (new Query())->from(File::tableName())->where(['id' => $asset->file_id])->one();
        self::assertIsArray($row);
        unset($row['id']);
        $row['basename'] .= '-other';

        File::getDb()->createCommand()->insert(File::tableName(), $row)->execute();

        return File::findOne(['basename' => $row['basename']]) ?? self::fail('The copy is missing.');
    }
}
