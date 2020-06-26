<?php
declare(strict_types=1);

namespace WPDumpSupport;

/**
 * WordPress wpdump
 */
class WPDump
{
  /**
   * @var string
   */
  private $dir;

  /**
   * @var WPJsonLoader
   */
  private $loader;

  /**
   * @var string[]
   */
  public $errors = [];

  /**
   * @var WPPost[]
   */
  public $posts = [];

  /**
   * @var WPCategory[]
   */
  public $categories = [];

  /**
   * @var WPUser[]
   */
  public $users = [];

  /**
   * @var WPTag[]
   */
  public $tags = [];

  /**
   * @var WPPage[]
   */
  public $pages = [];

  /**
   * @var WPMedia[]
   */
  public $mediaList = [];

  /**
   * @param $dir string
   */
  public function __construct($dir)
  {
    $this->dir = $dir;
    $this->loader = new WPJsonLoader();
  }

  /**
   * Reset
   */
  public function reset()
  {
    $this->errors = [];
    $this->posts = [];
    $this->pages = [];
    $this->categories = [];
    $this->users = [];
    $this->tags = [];
    $this->mediaList = [];
  }

  /**
   * Load all json.
   */
  public function load()
  {
    $this->reset();

    foreach ($this->loader->loadTags($this->fn('tags')) as $tag)
    {
      $this->tags[$tag->id] = $tag;
    }

    foreach ($this->loader->loadCategories($this->fn('categories')) as $category)
    {
      $this->categories[$category->id] = $category;
    }

    foreach ($this->loader->loadUsers($this->fn('users')) as $user)
    {
      $this->users[$user->id] = $user;
    }

    foreach ($this->loader->loadMediaList($this->fn('media')) as $media)
    {
      $media->author = $this->reference($media, 'author', $media->author, 'users');
      $this->mediaList[$media->id] = $media;
    }

    foreach ($this->loader->loadPages($this->fn('pages')) as $page)
    {
      $page->author = $this->reference($page, 'author', $page->author, 'users');
      $page->featured_media = $this->reference($page, 'featured_media', $page->featured_media, 'mediaList');
      $this->pages[$page->id] = $page;
    }

    foreach ($this->loader->loadPosts($this->fn('posts')) as $post)
    {
      $post->author = $this->reference($post, 'author', $post->author, 'users');
      $post->featured_media = $this->reference($post, 'featured_media', $post->featured_media, 'mediaList');
      for ($i = 0; $i < count($post->categories); ++$i)
      {
        $post->categories[$i] = $this->reference($post, 'categories', $post->categories[$i], 'categories');
      }
      for ($i = 0; $i < count($post->tags); ++$i)
      {
        $post->tags[$i] = $this->reference($post, 'tags', $post->tags[$i], 'tags');
      }
      $this->posts[$post->id] = $post;
    }
  }

  /**
   * @param $self WPObject
   * @param $prop string
   * @param $selfId int
   * @param $target string
   * @return WPObject
   */
  private function reference($self, $prop, $selfId, $target)
  {
    if (!$selfId)
    {
      return null;
    }

    if (!isset($this->$target[$selfId]))
    {
      $this->errors[] = sprintf("%s(%d): %s(%d) not found", get_class($self), $self->id, $prop, $selfId);

      return null;
    }

    return $this->$target[$selfId];
  }

  /**
   * @param $path string
   * @return string
   */
  public function fn($path)
  {
    return $this->dir . '/' . $path . '.json';
  }
}