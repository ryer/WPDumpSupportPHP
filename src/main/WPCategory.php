<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * category
 *
 * @property int $count
 * @property string $description
 * @property string $link
 * @property string $name
 * @property string $slug
 * @property string $taxonomy
 * @property int $parent
 * @property array $meta
 */
class WPCategory extends WPObject
{
  /**
   * @param array $source
   */
  public function processSource(array $source)
  {
    parent::processSource($source);

    if (!isset($source['taxonomy']))
    {
      throw new Exception("unexpected source");
    }
  }
}
