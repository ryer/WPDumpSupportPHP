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
   */
  public function processSource(array $source)
  {
    parent::processSource($source);

    if (!isset($source['url']))
    {
      throw new Exception("unexpected source");
    }
  }
}
