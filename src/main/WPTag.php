<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * tag
 *
 */
class WPTag extends WPObject
{
  public $count;
  public $description;
  public $link;
  public $name;
  public $slug;
  public $taxonomy;
  public $meta;

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

    $tag = new WPTag();
    $tag->id = $source['id'];
    $tag->count = $source['count'];
    $tag->description = $source['description'];
    $tag->link = $source['link'];
    $tag->name = $source['name'];
    $tag->slug = $source['slug'];
    $tag->taxonomy = $source['taxonomy'];
    $tag->meta = $source['meta'];

    return $tag;
  }
}
