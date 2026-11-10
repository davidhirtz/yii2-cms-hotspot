<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\Entry;
use Hirtz\Cms\Models\EntryReference;

class HotspotEntryReferenceTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testTheHotspotIsRebuiltByTheCommand(): void
    {
        self::assertContains(Hotspot::class, Entry::getModule()->getEntryReferenceModelClasses());
    }

    public function testAHotspotLinkingAnEntryIsIndexed(): void
    {
        $hotspot = Hotspot::findOne(1) ?? self::fail('No hotspot.');
        $hotspot->setAttribute('link', 'entry:4');
        $hotspot->setAttribute('content', '<p><a href="entry:4#Ågot">東京 🚀</a></p>');

        self::assertTrue($hotspot->save(), implode(' ', $hotspot->getErrorSummary(true)));

        $reference = EntryReference::findOne(['model_class' => Hotspot::class, 'model_id' => $hotspot->id]);

        self::assertSame(4, $reference?->entry_id);
        self::assertSame(['content' => 1, 'link' => 1], $reference->attribute_counts);

        self::assertSame(1, $hotspot->delete());
        self::assertNull(EntryReference::findOne(['model_class' => Hotspot::class, 'model_id' => $hotspot->id]));
    }
}
