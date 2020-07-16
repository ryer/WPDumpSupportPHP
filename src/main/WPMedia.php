<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * media
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
 * @property int|WPUser $author
 * @property string $comment_status
 * @property string $ping_status
 * @property string $template
 * @property array $meta
 * @property string $description
 * @property string $caption
 * @property string $alt_text
 * @property string $media_type
 * @property string $mime_type
 * @property array $media_details
 * @property string $post
 * @property string $source_url
 */
class WPMedia extends WPObject
{
  public $guid;
  public $title;
  public $description;
  public $caption;

  /**
   * @param array $source
   * @return WPMedia
   */
  public static function processSource(array $source)
  {
    if (!isset($source['id']) or !isset($source['type']) or $source['type'] !== 'attachment')
    {
      throw new Exception("unexpected source");
    }

    $self = new WPMedia();
    $self->id = $source['id'];
    $self->source = $source;

    $self->guid = $source['guid']['rendered'];
    $self->title = $source['title']['rendered'];
    $self->description = $source['description']['rendered'];
    $self->caption = $source['caption']['rendered'];

    return $self;
  }
}
