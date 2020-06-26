<?php
declare(strict_types=1);

namespace WPDumpSupportTest;

use PHPUnit\Framework\TestCase;
use WPDumpSupport\WPDump;

/**
 * Test
 */
class WPDumpTest extends TestCase
{
  public function testLoad()
  {
    $wpDump = new WPDump(__DIR__ . '/json/');
    $wpDump->load();
    static::assertCount(2, $wpDump->errors);
    static::assertEquals($wpDump->users[11]->id, $wpDump->mediaList[8484]->author->id);
    static::assertEquals($wpDump->users[11]->id, $wpDump->posts[8455]->author->id);
    static::assertEquals($wpDump->mediaList[8484]->id, $wpDump->posts[8455]->featured_media->id);
    static::assertEquals($wpDump->categories[3]->id, $wpDump->posts[8455]->categories[0]->id);
    static::assertEquals($wpDump->tags[27]->id, $wpDump->posts[8455]->tags[0]->id);
    static::assertEquals($wpDump->users[22]->id, $wpDump->pages[11011786]->author->id);
    static::assertEquals($wpDump->mediaList[8484]->id, $wpDump->pages[11011786]->featured_media->id);
  }
}
