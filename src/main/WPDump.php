<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Generator;
use WPDumpSupport\Resolver\ReferenceResolver;
use WPDumpSupport\Object\WPCategory;
use WPDumpSupport\Object\WPMedia;
use WPDumpSupport\Object\WPPage;
use WPDumpSupport\Object\WPPost;
use WPDumpSupport\Object\WPTag;
use WPDumpSupport\Object\WPUser;

/**
 * WordPress wpdump
 *
 * @method Generator|WPPost[] streamPosts()
 * @method Generator|WPPage[] streamPages()
 * @method Generator|WPMedia[] streamMedia()
 * @method Generator|WPUser[] streamUsers()
 * @method Generator|WPCategory[] streamCategories()
 * @method Generator|WPTag[] streamTags()
 * @method int countPosts()
 * @method int countPages()
 * @method int countMedia()
 * @method int countUsers()
 * @method int countCategories()
 * @method int countTags()
 */
class WPDump
{
  /**
   * @var ReferenceResolver
   */
  private $resolver;

  /**
   * @param array $params
   */
  public function __construct(array $params)
  {
    $this->resolver = new ReferenceResolver($params);
  }

  /**
   * Load all json.
   */
  public function load(): void
  {
    $this->resolver->load();
  }

  /**
   * Dynamically handle calls to stream<PostType>() methods.
   *
   * @param string $name
   * @param array $arguments
   * @return Generator|int
   */
  public function __call($name, $arguments)
  {
    try
    {
      if (strpos($name, 'stream') === 0)
      {
        $postTypeKey = strtolower(substr($name, 6)); // e.g., "streamPosts" -> "posts"

        return $this->resolver->stream($postTypeKey);
      }
      if (strpos($name, 'count') === 0)
      {
        $postTypeKey = strtolower(substr($name, 5)); // e.g., "countPosts" -> "posts"

        return $this->resolver->count($postTypeKey);
      }
    }
    catch (\InvalidArgumentException $e)
    {
      // Wrap the invalid argument exception in a bad method call exception
      // to provide a more user-friendly error for the facade user.
      throw new \BadMethodCallException("Method $name does not exist.", 0, $e);
    }

    throw new \BadMethodCallException("Method $name does not exist.");
  }
}
