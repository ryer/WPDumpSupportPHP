<?php
declare(strict_types=1);

namespace WPDumpSupportTest;

use PHPUnit\Framework\TestCase;
use WPDumpSupport\Error;
use WPDumpSupport\Resolver\ReferenceResolver;
use WPDumpSupport\WPDump;
use WPDumpSupport\Object\WPCategory;
use WPDumpSupport\Object\WPPost;
use WPDumpSupport\Object\WPUser;

/**
 * Test
 */
class WPDumpTest extends TestCase
{
  private $jsonDir;
  private $cacheDir;

  protected function setUp(): void
  {
    $this->jsonDir = __DIR__ . '/json/';
    $this->cacheDir = __DIR__ . '/cache/';
    mkdir($this->cacheDir, 0755, true);
  }

  protected function tearDown(): void
  {
    // Clean up cache files
    if (is_dir($this->cacheDir))
    {
      $this->rrmdir($this->cacheDir);
    }
  }

  public function testStreamPostsSuccessfully()
  {
    $customDefs = [
      // Override the default 'posts' definition to use a file with only valid references
      'posts' => ['cache' => 'file', 'class' => WPPost::class, 'file' => 'posts_valid.json'],
    ];
    $errorHandler = function (Error $error) {
      $this->fail("Error handler was called unexpectedly: " . $error->getMessage());
    };
    $wpDump = new WPDump(['jsonDir' => $this->jsonDir, 'cacheDir' => $this->cacheDir, 'customDefs' => $customDefs, 'errorHandler' => $errorHandler]);
    $wpDump->load();

    // Check if cache files are created
    $this->assertDirectoryExists($this->cacheDir . '/posts');
    $this->assertFileExists($this->cacheDir . '/posts/00/00/00/01/1.cache');

    $postCount = 0;
    $helloWorldPostFound = false;
    foreach ($wpDump->streamPosts() as $post)
    {
      if ($post->id === 1)
      {
        $helloWorldPostFound = true;
        $this->assertInstanceOf(WPUser::class, $post->author);
        $this->assertEquals(11, $post->author->id);
        $this->assertEquals('Anonymous man', $post->author->name);
        $this->assertEquals('Hello world!', $post->title);
        $this->assertEquals(0, $post->featured_media); // featured_media: 0 should remain 0
        // Assert that valid references are resolved
        $this->assertIsArray($post->categories);
        $this->assertCount(1, $post->categories);
        $this->assertInstanceOf(WPCategory::class, $post->categories[0]);
        $this->assertEquals(1, $post->categories[0]->id);
      }
      if ($post->id === 8455)
      {
        $this->assertNotNull($post->featured_media);
        $this->assertEquals(8484, $post->featured_media->id);
      }
      $postCount++;
    }
    $this->assertTrue($helloWorldPostFound, "Post with ID 1 ('Hello world!') not found.");
    $this->assertEquals(2, $postCount, 'Should stream all posts');
  }

  public function testStreamPagesSuccessfully()
  {
    $errorHandler = function (Error $error) {
      $this->fail("Error handler was called unexpectedly: " . $error->getMessage());
    };
    $wpDump = new WPDump(['jsonDir' => $this->jsonDir, 'cacheDir' => $this->cacheDir, 'errorHandler' => $errorHandler]);
    $wpDump->load();

    $pageCount = 0;
    foreach ($wpDump->streamPages() as $page)
    {
      $this->assertInstanceOf(WPUser::class, $page->author);
      $this->assertEquals(22, $page->author->id);
      $this->assertEquals('Frequently Asked Questions', $page->title);
      $pageCount++;
    }
    $this->assertEquals(1, $pageCount, 'Should stream all pages');
  }

  public function testCustomTypesSuccessfully()
  {
    $customDefs = [
      'books' => ['cache' => 'file', 'class' => Book::class, 'file' => 'books.json'],
      'genres' => ['cache' => 'memory', 'class' => Genre::class, 'file' => 'genres.json'],
    ];
    $errorHandler = function (Error $error) {
      $this->fail("Error handler was called unexpectedly: " . $error->getMessage());
    };
    $wpDump = new WPDump(['jsonDir' => $this->jsonDir, 'cacheDir' => $this->cacheDir, 'customDefs' => $customDefs, 'errorHandler' => $errorHandler]);
    $wpDump->load();

    $this->assertDirectoryExists($this->cacheDir . '/books');

    $bookCount = 0;
    foreach ($wpDump->streamBooks() as $book)
    {
      $this->assertInstanceOf(Book::class, $book);
      // Check author reference
      $this->assertInstanceOf(WPUser::class, $book->author);
      $this->assertEquals(11, $book->author->id);
      // Check custom taxonomy reference
      $this->assertIsArray($book->genres);
      $this->assertCount(1, $book->genres);
      $this->assertInstanceOf(Genre::class, $book->genres[0]);
      $this->assertEquals(101, $book->genres[0]->id);
      $bookCount++;
    }
    $this->assertEquals(1, $bookCount);
  }

  public function testMemoryCacheResolvesReferences()
  {
    $customDefs = [
      // Override posts to use memory cache
      'posts' => ['cache' => 'memory', 'class' => WPPost::class, 'file' => 'posts_valid.json'],
    ];
    $errorHandler = function (Error $error) {
      $this->fail("Error handler was called unexpectedly: " . $error->getMessage());
    };
    $wpDump = new WPDump(['jsonDir' => $this->jsonDir, 'cacheDir' => $this->cacheDir, 'customDefs' => $customDefs, 'errorHandler' => $errorHandler]);
    $wpDump->load();

    // Memory cache should not create files
    $this->assertDirectoryDoesNotExist($this->cacheDir . '/posts');

    $postCount = 0;
    foreach ($wpDump->streamPosts() as $post)
    {
      if ($post->id === 1)
      {
        $this->assertInstanceOf(WPUser::class, $post->author);
        $this->assertEquals(11, $post->author->id);
      }
      $postCount++;
    }
    $this->assertEquals(2, $postCount);
  }

  public function testReferenceErrorIsLogged()
  {
    $errors = [];
    $errorHandler = function (Error $error) use (&$errors) {
      $errors[] = $error;
    };
    $wpDump = new WPDump(['jsonDir' => $this->jsonDir, 'cacheDir' => $this->cacheDir, 'errorHandler' => $errorHandler]);
    $wpDump->load();

    // Must consume the generator to trigger reference resolution
    iterator_to_array($wpDump->streamPosts());

    $this->assertGreaterThan(0, count($errors));
    $errorMessages = array_map(function (Error $e) {
      return $e->getMessage();
    }, $errors);
    $this->assertContains('WPDumpSupport\Object\WPPost(1): featured_media(9999) not found in media', $errorMessages);
  }

  public function testFileNotFoundNoticeIsLogged()
  {
    $errors = [];
    $errorHandler = function (Error $error) use (&$errors) {
      $errors[] = $error;
    };
    $customDefs = [
      'nonexistent' => ['cache' => 'memory', 'class' => WPPost::class, 'file' => 'nonexistent.json'],
    ];
    $wpDump = new WPDump(['jsonDir' => $this->jsonDir, 'cacheDir' => $this->cacheDir, 'customDefs' => $customDefs, 'errorHandler' => $errorHandler]);
    $wpDump->load();

    $this->assertCount(1, $errors);
    /** @var Error $error */
    $error = $errors[0];
    $this->assertEquals(Error::NOTICE, $error->getLevel());
    $this->assertEquals('File not found, skipping: nonexistent.json', $error->getMessage());
  }

  public function testStreamEmptyJson()
  {
    $errorHandler = function (Error $error) {
      $this->fail("Error handler was called unexpectedly: " . $error->getMessage());
    };
    $customDefs = [
      'empty_posts' => ['cache' => 'memory', 'class' => WPPost::class, 'file' => 'empty.json'],
    ];
    $wpDump = new WPDump(['jsonDir' => $this->jsonDir, 'cacheDir' => $this->cacheDir, 'customDefs' => $customDefs, 'errorHandler' => $errorHandler]);
    $wpDump->load();

    $postCount = 0;
    foreach ($wpDump->streamEmpty_posts() as $post)
    {
      $postCount++;
    }
    $this->assertEquals(0, $postCount);
  }

  public function testUnknownCacheStrategyThrowsException()
  {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage("Unknown cache strategy: foo");

    $customDefs = [
      'posts' => ['cache' => 'foo', 'class' => WPPost::class, 'file' => 'posts.json'],
    ];
    new WPDump(['jsonDir' => $this->jsonDir, 'cacheDir' => $this->cacheDir, 'customDefs' => $customDefs]);
  }

  public function testCountMethods()
  {
    $customDefs = [
      // Define only the types we want to test
      'posts' => ['cache' => 'file', 'class' => WPPost::class, 'file' => 'posts_valid.json'], // 2 posts
      'pages' => ['cache' => 'memory', 'class' => WPPost::class, 'file' => 'pages.json'], // 1 page
      'empty' => ['cache' => 'file', 'class' => WPPost::class, 'file' => 'empty.json'], // 0 posts
      // Explicitly override all default types to prevent them from being loaded
      'media' => ['cache' => 'file', 'class' => WPPost::class, 'file' => 'nonexistent.json'],
      'users' => ['cache' => 'memory', 'class' => WPUser::class, 'file' => 'nonexistent.json'],
      'categories' => ['cache' => 'memory', 'class' => WPCategory::class, 'file' => 'nonexistent.json'],
      'tags' => ['cache' => 'memory', 'class' => WPCategory::class, 'file' => 'nonexistent.json'],
    ];
    $wpDump = new WPDump(['jsonDir' => $this->jsonDir, 'cacheDir' => $this->cacheDir, 'customDefs' => $customDefs]);
    $wpDump->load();

    // Test count for file cache
    $this->assertEquals(2, $wpDump->countPosts());
    // Test count for memory cache
    $this->assertEquals(1, $wpDump->countPages());
    // Test count for empty data
    $this->assertEquals(0, $wpDump->countEmpty());
    // Test count for default types that were overridden and not loaded
    $this->assertEquals(0, $wpDump->countMedia());
    $this->assertEquals(0, $wpDump->countUsers());

    // Test non-existent type
    $this->expectException(\BadMethodCallException::class);
    $wpDump->countNonExistent();
  }

  // Helper to remove directory recursively
  private function rrmdir($dir)
  {
    if (is_dir($dir))
    {
      $objects = scandir($dir);
      foreach ($objects as $object)
      {
        if ($object != "." && $object != "..")
        {
          if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object))
          {
            $this->rrmdir($dir . "/" . $object);
          }
          else
          {
            unlink($dir . "/" . $object);
          }
        }
      }
      rmdir($dir);
    }
  }
}

// Test-specific classes for custom post types and taxonomies
class Genre extends WPCategory
{
}

class Book extends WPPost
{
  public function resolveReferences(ReferenceResolver $resolver)
  {
    parent::resolveReferences($resolver);
    if (isset($this->genres) && is_array($this->genres))
    {
      $genres = [];
      foreach ($this->genres as $id)
      {
        if (is_int($id))
        {
          $genres[] = $resolver->resolveProperty($this, 'genres', $id, 'genres');
        }
      }
      $this->genres = array_filter($genres);
    }
  }
}
