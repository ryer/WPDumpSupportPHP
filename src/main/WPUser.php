<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * user
 */
class WPUser extends WPObject
{
  public $name;
  public $url;
  public $description;
  public $link;
  public $slug;
  public $avatar_urls;
  public $meta;

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

    $user = new WPUser();
    $user->id = $source['id'];
    $user->name = $source['name'];
    $user->url = $source['url'];
    $user->description = $source['description'];
    $user->link = $source['link'];
    $user->slug = $source['slug'];
    $user->avatar_urls = isset($source['avatar_urls']) ? $source['avatar_urls'] : [];
    $user->meta = $source['meta'];

    return $user;
  }
}
