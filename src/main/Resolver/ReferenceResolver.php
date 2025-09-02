<?php
declare(strict_types=1);

namespace WPDumpSupport\Resolver;

use Generator;
use RuntimeException;
use WPDumpSupport\Error;
use WPDumpSupport\Object\WPObject;
use WPDumpSupport\WPJsonLoader;
use WPDumpSupport\Object\WPCategory;
use WPDumpSupport\Object\WPMedia;
use WPDumpSupport\Object\WPPage;
use WPDumpSupport\Object\WPPost;
use WPDumpSupport\Object\WPTag;
use WPDumpSupport\Object\WPUser;

class ReferenceResolver
{
  /**
   * @var string
   */
  private $jsonDir;

  /**
   * @var string
   */
  private $cacheDir;

  /**
   * @var WPJsonLoader
   */
  private $loader;

  /**
   * @var array
   */
  private $dataTypeDefinitions = [];

  /**
   * @var ICacheStrategy[]
   */
  private $strategies = [];

  /**
   * @var callable|null
   */
  private $errorHandler;

  /**
   * @param array $params
   */
  public function __construct(array $params)
  {
    $this->jsonDir = $params['jsonDir'] ?? '';
    if (!is_dir($this->jsonDir))
    {
      throw new RuntimeException("JSON directory does not exist: {$this->jsonDir}");
    }
    $this->cacheDir = $params['cacheDir'] ?? '';
    if (!is_dir($this->cacheDir))
    {
      throw new RuntimeException("Cache directory does not exist: {$this->cacheDir}");
    }
    $this->loader = new WPJsonLoader();
    $this->errorHandler = $params['errorHandler'] ?? null;

    $defaultDefs = [
      'posts' => ['cache' => 'file', 'class' => WPPost::class, 'file' => 'posts.json'],
      'pages' => ['cache' => 'file', 'class' => WPPage::class, 'file' => 'pages.json'],
      'media' => ['cache' => 'file', 'class' => WPMedia::class, 'file' => 'media.json'],
      'users' => ['cache' => 'memory', 'class' => WPUser::class, 'file' => 'users.json'],
      'categories' => ['cache' => 'memory', 'class' => WPCategory::class, 'file' => 'categories.json'],
      'tags' => ['cache' => 'memory', 'class' => WPTag::class, 'file' => 'tags.json'],
    ];

    $this->dataTypeDefinitions = array_merge($defaultDefs, $params['customDefs'] ?? []);

    foreach ($this->dataTypeDefinitions as $key => $def)
    {
      switch ($def['cache'])
      {
        case 'file':
          $this->strategies[$key] = new FileCacheStrategy($this->cacheDir);
          break;
        case 'memory':
          $this->strategies[$key] = new MemoryCacheStrategy();
          break;
        default:
          throw new RuntimeException("Unknown cache strategy: " . $def['cache']);
      }
    }
  }

  /**
   * Load all json.
   */
  public function load(): void
  {
    $this->reset();

    foreach ($this->dataTypeDefinitions as $key => $def)
    {
      $filePath = $this->jsonDir . '/' . $def['file'];
      if (!file_exists($filePath))
      {
        $this->handleError(Error::NOTICE, "File not found, skipping: {$def['file']}");
        continue;
      }

      $strategy = $this->strategies[$key];
      foreach ($this->loader->streamWpJson($filePath, $def['class']) as $object)
      {
        try
        {
          $strategy->set($key, $object->id, $object);
        }
        catch (RuntimeException $e)
        {
          $this->handleError(Error::ERROR, $e->getMessage());
        }
      }
    }
  }

  /**
   * @param string $postTypeKey
   * @return Generator|WPObject[]
   */
  public function stream(string $postTypeKey): Generator
  {
    if (!isset($this->dataTypeDefinitions[$postTypeKey]))
    {
      throw new \InvalidArgumentException("Invalid data type key: {$postTypeKey}");
    }
    $def = $this->dataTypeDefinitions[$postTypeKey];
    $filePath = $this->jsonDir . '/' . $def['file'];

    foreach ($this->loader->streamWpJson($filePath, $def['class']) as $object)
    {
      yield $this->resolve($object);
    }
  }

  /**
   * @param string $postTypeKey
   * @return int
   */
  public function count(string $postTypeKey): int
  {
    if (!isset($this->dataTypeDefinitions[$postTypeKey]))
    {
      throw new \InvalidArgumentException("Invalid data type key: {$postTypeKey}");
    }

    return $this->strategies[$postTypeKey]->count($postTypeKey);
  }

  /**
   * @param WPObject $object
   * @return WPObject
   */
  public function resolve(WPObject $object): WPObject
  {
    $object->resolveReferences($this);

    return $object;
  }

  /**
   * @param WPObject $self
   * @param string $prop
   * @param int $id
   * @param string $targetKey
   * @return WPObject|null
   */
  public function resolveProperty(WPObject $self, string $prop, int $id, string $targetKey): ?WPObject
  {
    if (!$id)
    {
      return null;
    }

    $strategy = $this->strategies[$targetKey] ?? null;
    if (!$strategy)
    {
      $this->handleError(Error::ERROR, sprintf("No cache strategy found for target key: %s", $targetKey));

      return null;
    }

    $object = $strategy->get($targetKey, $id);

    if (!$object)
    {
      $this->handleError(Error::WARNING, sprintf("%s(%d): %s(%d) not found in %s", get_class($self), $self->id, $prop, $id, $targetKey));

      return null;
    }

    return $object;
  }

  /**
   * Reset
   */
  private function reset()
  {
    foreach ($this->strategies as $strategy)
    {
      $strategy->reset();
    }
  }

  /**
   * @param string $level
   * @param string $message
   */
  private function handleError(string $level, string $message)
  {
    if ($this->errorHandler)
    {
      call_user_func($this->errorHandler, new Error($level, $message));
    }
  }
}