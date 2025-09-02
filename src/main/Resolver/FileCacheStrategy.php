<?php
declare(strict_types=1);

namespace WPDumpSupport\Resolver;

use RuntimeException;
use WPDumpSupport\Object\WPObject;

class FileCacheStrategy implements ICacheStrategy
{
  /**
   * @var string
   */
  private $cacheDir;

  /**
   * @var int[]
   */
  private $counts = [];

  public function __construct(string $cacheDir)
  {
    $this->cacheDir = $cacheDir;
  }

  public function set(string $key, int $id, WPObject $object): void
  {
    if (!isset($this->counts[$key]))
    {
      $this->counts[$key] = 0;
    }
    $this->counts[$key]++;

    $cachePath = $this->generateCachePath($key, $id);
    $ret = file_put_contents($cachePath, serialize($object));
    if ($ret === false)
    {
      throw new RuntimeException(sprintf("Failed to write cache for %s(%d)", get_class($object), $id));
    }
  }

  public function get(string $key, int $id): ?WPObject
  {
    $cachePath = $this->generateCachePath($key, $id);
    if (file_exists($cachePath))
    {
      return unserialize(file_get_contents($cachePath));
    }

    return null;
  }

  public function reset(): void
  {
    $this->counts = [];
  }

  public function count(string $key): int
  {
    return $this->counts[$key] ?? 0;
  }

  /**
   * @param string $key
   * @param int $id
   * @return string
   */
  private function generateCachePath(string $key, int $id): string
  {
    $padding = sprintf("%08d", $id);
    $subDirs = str_split($padding, 2);

    $cacheSubDir = $this->cacheDir . '/' . $key . '/' . implode('/', $subDirs);
    if (!is_dir($cacheSubDir))
    {
      mkdir($cacheSubDir, 0755, true);
    }

    return $cacheSubDir . '/' . $id . '.cache';
  }
}