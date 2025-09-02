<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;
use Generator;
use JsonMachine\Items;
use WPDumpSupport\Object\WPObject;

/**
 * WordPress wp-json loader using JsonMachine
 */
class WPJsonLoader
{
  /**
   * @param string $filePath
   * @param string $className WPObject subclass name
   * @return Generator|WPObject[]
   * @throws Exception
   */
  public function streamWpJson($filePath, $className): Generator
  {
    if (!file_exists($filePath))
    {
      // Instead of throwing an exception, we return an empty generator
      // because some json files might be optional.
      yield from [];

      return;
    }

    $sources = Items::fromFile($filePath, ['decoder' => new \JsonMachine\JsonDecoder\ExtJsonDecoder(true)]);
    foreach ($sources as $source)
    {
      /** @var WPObject $o */
      $o = new $className();
      $o->processSource($source);
      yield $o;
    }
  }
}
