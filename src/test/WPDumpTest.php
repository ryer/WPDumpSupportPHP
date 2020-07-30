<?php
declare(strict_types=1);

namespace WPDumpSupportTest;

use PHPUnit\Framework\TestCase;
use WPDumpSupport\WPCategory;
use WPDumpSupport\WPDump;

/**
 * Test
 */
class WPDumpTest extends TestCase
{
  public function testLoad()
  {
    $wpDump = new WPDump(__DIR__ . '/json/', ['custom' => WPCategory::class]);
    $wpDump->load();
    static::assertCount(2, $wpDump->errors);

    // media.author -> user.id
    static::assertEquals($wpDump->users[11]->id, $wpDump->mediaList[8484]->author->id);

    // posts.author -> user.id
    static::assertEquals($wpDump->users[11]->id, $wpDump->posts[8455]->author->id);

    // posts.featured_media -> media.id
    static::assertEquals($wpDump->mediaList[8484]->id, $wpDump->posts[8455]->featured_media->id);

    // posts.categories -> categories.id
    static::assertEquals($wpDump->categories[3]->id, $wpDump->posts[8455]->categories[0]->id);

    // posts.tags -> tags.id
    static::assertEquals($wpDump->tags[27]->id, $wpDump->posts[8455]->tags[0]->id);

    // pages.author -> user.id
    static::assertEquals($wpDump->users[22]->id, $wpDump->pages[11011786]->author->id);

    // pages.featured_media -> media.id
    static::assertEquals($wpDump->mediaList[8484]->id, $wpDump->pages[11011786]->featured_media->id);

    // custom
    /* @noinspection PhpPossiblePolymorphicInvocationInspection */
    static::assertEquals('Community', $wpDump->custom['custom'][3]->name);
  }
}
