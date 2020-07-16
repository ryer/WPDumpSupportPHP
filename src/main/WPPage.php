<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * page
 *
 * @property string $date
 * @property string $date_gmt
 * @property string $guid
 * @property string $modified
 * @property string $modified_gmt
 * @property string $slug
 * @property string $status
 * @property string $type
 * @property string $link
 * @property string $title
 * @property string $content
 * @property string $excerpt
 * @property int|WPUser $author
 * @property int|WPMedia $featured_media
 * @property string $comment_status
 * @property string $ping_status
 * @property string $template
 * @property array $meta
 */
class WPPage extends WPObject
{
  public $guid;
  public $title;
  public $content;
  public $excerpt;

  /**
   * @param array $source
   * @return WPPage
   */
  public static function processSource(array $source)
  {
    if (!isset($source['id']) or !isset($source['type']) or $source['type'] !== 'page')
    {
      throw new Exception("unexpected source");
    }

    $self = new WPPage();
    $self->id = $source['id'];
    $self->source = $source;

    $self->guid = $source['guid']['rendered'];
    $self->title = $source['title']['rendered'];
    $self->content = $source['content']['rendered'];
    $self->excerpt = $source['excerpt']['rendered'];

    return $self;
  }
}
