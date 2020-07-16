<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * user
 *
 * @property string $name
 * @property string $url
 * @property string $description
 * @property string $link
 * @property string $slug
 * @property string[] $avatar_urls
 * @property string $meta
 */
class WPUser extends WPObject
{
  /**
   * @param array $source
   * @return WPUser
   */
  public static function processSource(array $source)
  {
    if (!isset($source['id']) or !isset($source['url']))
    {
      throw new Exception("unexpected source");
    }

    $self = new WPUser();
    $self->id = $source['id'];
    $self->source = $source;

    return $self;
  }
}
