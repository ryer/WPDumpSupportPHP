<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * tag
 *
 * @property int $count
 * @property string $description
 * @property string $link
 * @property string $name
 * @property string $slug
 * @property string $taxonomy
 * @property array $meta
 */
class WPTag extends WPObject
{
  /**
   * @param array $source
   * @return WPTag
   */
  public static function processSource(array $source)
  {
    if (!isset($source['id']) or !isset($source['taxonomy']) or $source['taxonomy'] !== 'post_tag')
    {
      throw new Exception("unexpected source");
    }

    $self = new WPTag();
    $self->id = $source['id'];
    $self->source = $source;

    return $self;
  }
}
