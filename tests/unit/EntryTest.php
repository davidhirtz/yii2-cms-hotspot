<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\tests\unit;

use Codeception\Test\Unit;
use Hirtz\Cms\models\Entry;

class EntryTest extends Unit
{
    public function testCreateEntry()
    {
        $entry = Entry::create();
        $entry->name = 'Test Entry';
        $entry->slug = $entry::getModule()->entryIndexSlug;

        $this->assertTrue($entry->save());
        $this->assertTrue($entry->isIndex());
    }
}
