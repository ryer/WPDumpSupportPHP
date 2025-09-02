<?php
declare(strict_types=1);

namespace WPDumpSupport\Object;

use WPDumpSupport\Resolver\ReferenceResolver;
use WPDumpSupport\Object\WPCategory;
use WPDumpSupport\Object\WPMedia;
use WPDumpSupport\Object\WPTag;
use WPDumpSupport\Object\WPUser;

/**
 * post
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
 * @property string $sticky
 * @property string $template
 * @property string $format
 * @property array $meta
 * @property int[]|WPCategory[] $categories
 * @property int[]|WPTag[] $tags
 */
class WPPost extends WPObject
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

    if (isset($this->categories) && is_array($this->categories))
    {
      $categories = [];
      foreach ($this->categories as $id)
      {
        if (is_int($id))
        {
          $categories[] = $resolver->resolveProperty($this, 'categories', $id, 'categories');
        }
      }
      $this->categories = array_filter($categories);
    }

    if (isset($this->tags) && is_array($this->tags))
    {
      $tags = [];
      foreach ($this->tags as $id)
      {
        if (is_int($id))
        {
          $tags[] = $resolver->resolveProperty($this, 'tags', $id, 'tags');
        }
      }
      $this->tags = array_filter($tags);
    }
  }
}