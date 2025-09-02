<?php
declare(strict_types=1);

namespace WPDumpSupport\Object;

use WPDumpSupport\Resolver\ReferenceResolver;
use WPDumpSupport\Object\WPMedia;
use WPDumpSupport\Object\WPUser;

/**
 * page
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
 * @property array $content
 * @property array $excerpt
 * @property int|WPUser $author
 * @property int|WPMedia $featured_media
 * @property string $comment_status
 * @property string $ping_status
 * @property string $template
 * @property array $meta
 */
class WPPage extends WPObject
{
  public function resolveReferences(ReferenceResolver $resolver)
  {
    if (isset($this->author) && is_int($this->author))
    {
      $this->author = $resolver->resolveProperty($this, 'author', $this->author, 'users');
    }

    if (isset($this->featured_media) && is_int($this->featured_media) && $this->featured_media > 0)
    {
      $this->featured_media = $resolver->resolveProperty($this, 'featured_media', $this->featured_media, 'media');
    }
  }
}