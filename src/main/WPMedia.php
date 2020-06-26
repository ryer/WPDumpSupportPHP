<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * media
 *
 * @property int|WPUser $author
 */
class WPMedia extends WPObject
{
  public $date;
  public $date_gmt;
  public $guid;
  public $modified;
  public $modified_gmt;
  public $slug;
  public $status;
  public $type;
  public $link;
  public $title;
  public $author;
  public $comment_status;
  public $ping_status;
  public $template;
  public $meta;
  public $description;
  public $caption;
  public $alt_text;
  public $media_type;
  public $mime_type;
  public $media_details;
  public $post;
  public $source_url;

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

    $media = new WPMedia();
    $media->id = $source['id'];
    $media->date = $source['date'];
    $media->date_gmt = $source['date_gmt'];
    $media->guid = $source['guid']['rendered'];
    $media->modified = $source['modified'];
    $media->modified_gmt = $source['modified_gmt'];
    $media->slug = $source['slug'];
    $media->status = $source['status'];
    $media->type = $source['type'];
    $media->link = $source['link'];
    $media->title = $source['title']['rendered'];
    $media->author = $source['author'];
    $media->comment_status = $source['comment_status'];
    $media->ping_status = $source['ping_status'];
    $media->template = $source['template'];
    $media->meta = $source['meta'];
    $media->description = $source['description']['rendered'];
    $media->caption = $source['caption']['rendered'];
    $media->alt_text = $source['alt_text'];
    $media->media_type = $source['media_type'];
    $media->mime_type = $source['mime_type'];
    $media->media_details = $source['media_details'];
    $media->post = $source['post'];
    $media->source_url = $source['source_url'];

    return $media;
  }
}
