<?php
declare(strict_types=1);

namespace WPDumpSupportTest;

use PHPUnit\Framework\TestCase;
use WPDumpSupport\Resolver\ICacheStrategy;
use WPDumpSupport\Error;
use WPDumpSupport\Object\WPObject;
use WPDumpSupport\Resolver\ReferenceResolver;

class ReferenceResolverTest extends TestCase
{
  private $cacheStrategyMock;
  private $resolver;
  private $selfObject;
  private $cacheDir;

  protected function setUp(): void
  {
    $this->cacheStrategyMock = $this->createMock(ICacheStrategy::class);
    $this->selfObject = new class extends WPObject {
      public function __construct()
      {
        $this->id = 1;
      }
    };

    $this->cacheDir = __DIR__ . '/cache';
    if (!is_dir($this->cacheDir))
    {
      mkdir($this->cacheDir);
    }

    // This test doesn't set an error handler, so errors should be ignored.
    $params = [
      'jsonDir' => __DIR__ . '/json/',
      'cacheDir' => $this->cacheDir,
      'customDefs' => [
        'posts' => ['cache' => 'memory', 'class' => WPObject::class, 'file' => 'posts.json']
      ]
    ];
    $this->resolver = new ReferenceResolver($params);

    // Since we are not using the real loader, we need to manually set the strategy mock
    $reflection = new \ReflectionClass($this->resolver);
    $strategiesProp = $reflection->getProperty('strategies');
    $strategiesProp->setAccessible(true);
    $strategiesProp->setValue($this->resolver, ['posts' => $this->cacheStrategyMock]);
  }

  public function testResolveSuccessfully()
  {
    $targetObject = new class extends WPObject {
    };
    $this->cacheStrategyMock->method('get')->willReturn($targetObject);

    $result = $this->resolver->resolveProperty($this->selfObject, 'author', 123, 'posts');
    $this->assertSame($targetObject, $result);
  }

  public function testResolveWithZeroId()
  {
    $result = $this->resolver->resolveProperty($this->selfObject, 'author', 0, 'posts');
    $this->assertNull($result);
  }

  public function testResolveWithNoCacheStrategy()
  {
    $errors = [];
    $errorHandler = function (Error $error) use (&$errors) {
      $errors[] = $error;
    };

    // Create a new resolver instance with the error handler
    $params = [
      'jsonDir' => __DIR__ . '/json/',
      'cacheDir' => $this->cacheDir,
      'errorHandler' => $errorHandler,
    ];
    $resolver = new ReferenceResolver($params);

    $result = $resolver->resolveProperty($this->selfObject, 'author', 123, 'unknown');
    $this->assertNull($result);

    $this->assertCount(1, $errors);
    $this->assertEquals(Error::ERROR, $errors[0]->getLevel());
    $this->assertEquals('No cache strategy found for target key: unknown', $errors[0]->getMessage());
  }

  public function testResolveWithObjectNotFound()
  {
    $errors = [];
    $errorHandler = function (Error $error) use (&$errors) {
      $errors[] = $error;
    };

    // Re-create resolver with the error handler for this test
    $params = [
      'jsonDir' => __DIR__ . '/json/',
      'cacheDir' => $this->cacheDir,
      'customDefs' => [
        'posts' => ['cache' => 'memory', 'class' => WPObject::class, 'file' => 'posts.json']
      ],
      'errorHandler' => $errorHandler,
    ];
    $this->resolver = new ReferenceResolver($params);

    // Manually set the mock again
    $reflection = new \ReflectionClass($this->resolver);
    $strategiesProp = $reflection->getProperty('strategies');
    $strategiesProp->setAccessible(true);
    $strategiesProp->setValue($this->resolver, ['posts' => $this->cacheStrategyMock]);

    $this->cacheStrategyMock->method('get')->willReturn(null);

    $result = $this->resolver->resolveProperty($this->selfObject, 'author', 123, 'posts');
    $this->assertNull($result);

    $this->assertCount(1, $errors);
    $this->assertEquals(Error::WARNING, $errors[0]->getLevel());
    $this->assertStringContainsString('not found in posts', $errors[0]->getMessage());
  }

  public function testConstructorWithMissingJsonDir()
  {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('JSON directory does not exist');
    new ReferenceResolver(['jsonDir' => '/non/existent/dir', 'cacheDir' => $this->cacheDir]);
  }

  public function testConstructorWithMissingCacheDir()
  {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Cache directory does not exist');
    new ReferenceResolver(['jsonDir' => __DIR__ . '/json/', 'cacheDir' => '/non/existent/dir']);
  }

  protected function tearDown(): void
  {
    if (is_dir($this->cacheDir))
    {
      $files = glob($this->cacheDir . '/*');
      foreach ($files as $file)
      {
        unlink($file);
      }
      rmdir($this->cacheDir);
    }
  }
}