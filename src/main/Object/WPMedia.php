<?php
declare(strict_types=1);

namespace WPDumpSupport\Object;

use WPDumpSupport\Resolver\ReferenceResolver;
use WPDumpSupport\Object\WPUser;

/**
 * media
 *
 * @property string $date
 * @property string $date_gmt
 * @property array $guid
 * @property string $modified
 * @property string $modified_gmt
 * @property string $slug
 * @property string $status
 * @property string $type
 * @property string $link
 * @property array $title
 * @property int|WPUser $author
 * @property string $comment_status
 * @property string $ping_status
 * @property string $template
 * @property array $meta
 * @property array $description
 * @property array $caption
 * @property string $alt_text
 * @property string $media_type
 * @property string $mime_type
 * @property array $media_details
 * @property string $post
 * @property string $source_url
 */
class WPMedia extends WPObject
{
  public function resolveReferences(ReferenceResolver $resolver)
  {
    if (property_exists($this, 'author') && is_int($this->author))
    {
      $this->author = $resolver->resolveProperty($this, 'author', $this->author, 'users');
    }
  }
}