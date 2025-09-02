<?php
declare(strict_types=1);

namespace WPDumpSupport\Resolver;

use WPDumpSupport\Object\WPObject;

class MemoryCacheStrategy implements ICacheStrategy
{
  /**
   * @var WPObject[][]
   */
  private $data = [];

  public function set(string $key, int $id, WPObject $object): void
  {
    if (!isset($this->data[$key]))
    {
      $this->data[$key] = [];
    }
    $this->data[$key][$id] = $object;
  }

  public function get(string $key, int $id): ?WPObject
  {
    return $this->data[$key][$id] ?? null;
  }

  public function reset(): void
  {
    $this->data = [];
  }

  public function count(string $key): int
  {
    return count($this->data[$key] ?? []);
  }
}