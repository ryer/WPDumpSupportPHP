<?php
declare(strict_types=1);

namespace WPDumpSupportTest;

use Generator;
use JsonMachine\Exception\SyntaxErrorException;
use PHPUnit\Framework\TestCase;
use WPDumpSupport\WPJsonLoader;
use WPDumpSupport\Object\WPTag;

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

  public function testStreamWpJson()
  {
    $tags = $this->loader->streamWpJson(__DIR__ . '/json/tags.json', WPTag::class);
    $this->assertInstanceOf(Generator::class, $tags);
    $tagArray = iterator_to_array($tags);
    $this->assertCount(2, $tagArray);
    $this->assertInstanceOf(WPTag::class, $tagArray[0]);
  }

  public function testStreamNonExistentFile()
  {
    $items = $this->loader->streamWpJson('non_existent_file.json', WPTag::class);
    $this->assertInstanceOf(Generator::class, $items);
    $this->assertCount(0, iterator_to_array($items));
  }

  public function testStreamEmptyJson()
  {
    $items = $this->loader->streamWpJson(__DIR__ . '/json/empty.json', WPTag::class);
    $this->assertCount(0, iterator_to_array($items));
  }

  public function testStreamInvalidJson()
  {
    $this->expectException(SyntaxErrorException::class);
    $items = $this->loader->streamWpJson(__DIR__ . '/json/invalid.json', WPTag::class);
    iterator_to_array($items);
  }
}
