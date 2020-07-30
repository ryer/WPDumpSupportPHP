<?php
declare(strict_types=1);

namespace WPDumpSupportTest;

use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use WPDumpSupport\WPCategory;
use WPDumpSupport\WPJsonLoader;
use WPDumpSupport\WPMedia;
use WPDumpSupport\WPPage;
use WPDumpSupport\WPPost;
use WPDumpSupport\WPTag;
use WPDumpSupport\WPUser;

/**
 * Test
 */
class WPJsonLoaderTest extends TestCase
{
  /**
   * @var WPJsonLoader
   */
  private $loader;

  public function setUp(): void
  {
    $this->loader = new WPJsonLoader();
  }

  public function testLoadWpJson()
  {
    $tags = $this->loader->loadWpJson(__DIR__ . '/json/tags.json', 'WPDumpSupport\WPTag');
    static::assertInstanceOf(Generator::class, $tags);
    static::assertInstanceOf(WPTag::class, iterator_to_array($tags)[0]);

    static::expectException(Exception::class);
    iterator_to_array($this->loader->loadWpJson('xxx', 'WPDumpSupport\WPTag'));
  }

  public function testLoadTags()
  {
    $tags = $this->loader->loadTags(__DIR__ . '/json/tags.json');
    static::assertInstanceOf(WPTag::class, iterator_to_array($tags)[0]);
  }

  public function testLoadPages()
  {
    $pages = $this->loader->loadPages(__DIR__ . '/json/pages.json');
    static::assertInstanceOf(WPPage::class, iterator_to_array($pages)[0]);
  }

  public function testLoadCategories()
  {
    $categories = $this->loader->loadCategories(__DIR__ . '/json/categories.json');
    static::assertInstanceOf(WPCategory::class, iterator_to_array($categories)[0]);
  }

  public function testLoadPosts()
  {
    $posts = $this->loader->loadPosts(__DIR__ . '/json/posts.json');
    static::assertInstanceOf(WPPost::class, iterator_to_array($posts)[0]);
  }

  public function testLoadMediaList()
  {
    $mediaList = $this->loader->loadMediaList(__DIR__ . '/json/media.json');
    static::assertInstanceOf(WPMedia::class, iterator_to_array($mediaList)[0]);
  }

  public function testLoadUsers()
  {
    $mediaList = $this->loader->loadUsers(__DIR__ . '/json/users.json');
    static::assertInstanceOf(WPUser::class, iterator_to_array($mediaList)[0]);
  }

  public function testLoadCustom()
  {
    $mediaList = $this->loader->loadCustom(__DIR__ . '/json/custom.json', WPCategory::class);
    static::assertInstanceOf(WPCategory::class, iterator_to_array($mediaList)[0]);
  }
}
