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
   * @return WPCategory
   */
  public static function processSource(array $source)
  {
    if (!isset($source['id']) or !isset($source['taxonomy']) or $source['taxonomy'] !== 'category')
    {
      throw new Exception("unexpected source");
    }

    $self = new WPCategory();
    $self->id = $source['id'];
    $self->source = $source;

    return $self;
  }
}
