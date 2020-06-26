<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * category
 */
class WPCategory extends WPObject
{
  public $count;
  public $description;
  public $link;
  public $name;
  public $slug;
  public $taxonomy;
  public $parent;
  public $meta;

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

    $category = new WPCategory();
    $category->id = $source['id'];
    $category->count = $source['count'];
    $category->description = $source['description'];
    $category->link = $source['link'];
    $category->name = $source['name'];
    $category->slug = $source['slug'];
    $category->taxonomy = $source['taxonomy'];
    $category->parent = $source['parent'];
    $category->meta = $source['meta'];

    return $category;
  }
}
