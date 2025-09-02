<?php
declare(strict_types=1);

namespace WPDumpSupportTest\Resolver;

use PHPUnit\Framework\TestCase;
use WPDumpSupport\Resolver\FileCacheStrategy;
use WPDumpSupport\Object\WPObject;
use WPDumpSupport\Resolver\ReferenceResolver;

class FileCacheStrategyTest extends TestCase
{
  private $cacheDir;

  protected function setUp(): void
  {
    $this->cacheDir = __DIR__ . '/cache_test/';
    if (!is_dir($this->cacheDir))
    {
      mkdir($this->cacheDir, 0755, true);
    }
  }

  protected function tearDown(): void
  {
    if (is_dir($this->cacheDir))
    {
      // Simple recursive delete for cleanup
      $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
      );
      foreach ($files as $fileinfo)
      {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
      }
      rmdir($this->cacheDir);
    }
  }

  public function testSetAndGet()
  {
    $strategy = new FileCacheStrategy($this->cacheDir);
    $object = new TestCacheableObject(1);

    $strategy->set('test', 1, $object);
    $this->assertFileExists($this->cacheDir . '/test/00/00/00/01/1.cache');

    $retrieved = $strategy->get('test', 1);
    $this->assertInstanceOf(get_class($object), $retrieved);
    $this->assertEquals($object->id, $retrieved->id);
  }

  public function testGetNonExistent()
  {
    $strategy = new FileCacheStrategy($this->cacheDir);
    $retrieved = $strategy->get('test', 1);
    $this->assertNull($retrieved);
  }
}

class TestCacheableObject extends WPObject
{
  public function __construct($id)
  {
    $this->id = $id;
  }

  public function resolveReferences(ReferenceResolver $resolver)
  {
    $resolver->resolveProperty($this, 'prop', 1, 'key');
  }
}