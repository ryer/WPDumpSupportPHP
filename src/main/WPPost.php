<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * post
 *
 * @property string $date;
 * @property string $date_gmt;
 * @property string $guid;
 * @property string $modified;
 * @property string $modified_gmt;
 * @property string $slug;
 * @property string $status;
 * @property string $type;
 * @property string $link;
 * @property string $title;
 * @property string $content;
 * @property string $excerpt;
 * @property int|WPUser $author
 * @property int|WPMedia $featured_media
 * @property string $comment_status;
 * @property string $ping_status;
 * @property string $sticky;
 * @property string $template;
 * @property string $format;
 * @property array $meta;
 * @property int[]|WPCategory[] $categories
 * @property int[]|WPTag[] $tags
 */
class WPPost extends WPObject
{
  public $guid;
  public $title;
  public $content;
  public $excerpt;

  /**
   * @param array $source
   */
  public function processSource(array $source)
  {
    parent::processSource($source);

    if (
      !isset($source['type']) or
      !isset($source['guid']) or
      !isset($source['title']) or
      !isset($source['content']) or
      !isset($source['excerpt'])
    )
    {
      throw new Exception("unexpected source");
    }

    $this->guid = $source['guid']['rendered'];
    $this->title = $source['title']['rendered'];
    $this->content = $source['content']['rendered'];
    $this->excerpt = $source['excerpt']['rendered'];
  }
}
