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
   */
  public function processSource(array $source)
  {
    parent::processSource($source);

    if (
      !isset($source['type']) or
      !isset($source['guid']) or
      !isset($source['title']) or
      !isset($source['description']) or
      !isset($source['caption'])
    )
    {
      throw new Exception("unexpected source");
    }

    $this->guid = $source['guid']['rendered'];
    $this->title = $source['title']['rendered'];
    $this->description = $source['description']['rendered'];
    $this->caption = $source['caption']['rendered'];
  }
}
