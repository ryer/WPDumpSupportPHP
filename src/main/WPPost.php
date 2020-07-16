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
   * @return WPPost
   */
  public static function processSource(array $source)
  {
    if (!isset($source['id']) or !isset($source['type']) or $source['type'] !== 'post')
    {
      throw new Exception("unexpected source");
    }

    $self = new WPPost();
    $self->id = $source['id'];
    $self->source = $source;

    $self->guid = $source['guid']['rendered'];
    $self->title = $source['title']['rendered'];
    $self->content = $source['content']['rendered'];
    $self->excerpt = $source['excerpt']['rendered'];

    return $self;
  }
}
