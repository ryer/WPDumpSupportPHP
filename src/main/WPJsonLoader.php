<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;
use Generator;

/**
 * WordPress wp-json
 */
class WPJsonLoader
{
  /**
   * @param string $filePath
   * @return Generator|WPPost[]
   */
  public function loadPosts($filePath): Generator
  {
    return $this->loadWpJson($filePath, ['WPDumpSupport\WPPost', 'processSource']);
  }

  /**
   * @param string $filePath
   * @return Generator|WPTag[]
   */
  public function loadTags($filePath): Generator
  {
    return $this->loadWpJson($filePath, ['WPDumpSupport\WPTag', 'processSource']);
  }

  /**
   * @param string $filePath
   * @return Generator|WPCategory[]
   */
  public function loadCategories($filePath): Generator
  {
    return $this->loadWpJson($filePath, ['WPDumpSupport\WPCategory', 'processSource']);
  }

  /**
   * @param string $filePath
   * @return Generator|WPMedia[]
   */
  public function loadMediaList($filePath): Generator
  {
    return $this->loadWpJson($filePath, ['WPDumpSupport\WPMedia', 'processSource']);
  }

  /**
   * @param string $filePath
   * @return Generator|WPPage[]
   */
  public function loadPages($filePath): Generator
  {
    return $this->loadWpJson($filePath, ['WPDumpSupport\WPPage', 'processSource']);
  }

  /**
   * @param string $filePath
   * @return Generator|WPUser[]
   */
  public function loadUsers($filePath): Generator
  {
    return $this->loadWpJson($filePath, ['WPDumpSupport\WPUser', 'processSource']);
  }

  /**
   * @param string $filePath
   * @param callable $processor (array $source): WPObject
   * @return Generator|WPObject[]
   */
  public function loadWpJson($filePath, $processor): Generator
  {
    if (!file_exists($filePath))
    {
      throw new Exception("File not found: $filePath");
    }

    $json = file_get_contents($filePath);
    if (!$json)
    {
      throw new Exception("Read error: $filePath");
    }

    $sources = json_decode($json, true);
    if (!$sources or !is_array($sources))
    {
      throw new Exception("Invalid json: $filePath");
    }
    unset($json);

    for ($i = 0; $i < count($sources); ++$i)
    {
      yield $processor($sources[$i]);
      $sources[$i] = null;
    }
  }
}
