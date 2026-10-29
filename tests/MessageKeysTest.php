<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests;

use Hirtz\Skeleton\Test\TestCase;
use Hirtz\Skeleton\Test\Traits\MessageKeysTrait;

class MessageKeysTest extends TestCase
{
    use MessageKeysTrait;

    public function testTheHotspotKeysFollowTheScheme(): void
    {
        self::assertMessageKeys(dirname(__DIR__) . '/messages', 'hotspot');
    }
}
