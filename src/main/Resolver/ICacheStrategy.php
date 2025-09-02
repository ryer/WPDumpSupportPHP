<?php
declare(strict_types=1);

namespace WPDumpSupport\Resolver;

use WPDumpSupport\Object\WPObject;

interface ICacheStrategy
{
  /**
   * @param string $key
   * @param int $id
   * @param WPObject $object
   * @return void
   */
  public function set(string $key, int $id, WPObject $object): void;

  /**
   * @param string $key
   * @param int $id
   * @return WPObject|null
   */
  public function get(string $key, int $id): ?WPObject;

  /**
   * @return void
   */
  public function reset(): void;

  /**
   * @param string $key
   * @return int
   */
  public function count(string $key): int;
}