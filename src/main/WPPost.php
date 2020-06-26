<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * post
 *
 * @property int|WPUser $author
 * @property int|WPMedia $featured_media
 * @property int[]|WPCategory[] $categories
 * @property int[]|WPTag[] $tags
 */
class WPPost extends WPObject
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
  public $content;
  public $excerpt;
  public $author;
  public $featured_media;
  public $comment_status;
  public $ping_status;
  public $sticky;
  public $template;
  public $format;
  public $meta;
  public $categories;
  public $tags;

  /**
   * @param array $source
   * @return WPPost
   */
  public static function processSource(array $source)
  {
    if (!isset($source['id']) or !isset($source['type']) or $source['type'] !== 'post')
    {
      throw new Exception("unexpected source");
    }

    $post = new WPPost();
    $post->id = $source['id'];
    $post->date = $source['date'];
    $post->date_gmt = $source['date_gmt'];
    $post->guid = $source['guid']['rendered'];
    $post->modified = $source['modified'];
    $post->modified_gmt = $source['modified_gmt'];
    $post->slug = $source['slug'];
    $post->status = $source['status'];
    $post->type = $source['type'];
    $post->link = $source['link'];
    $post->title = $source['title']['rendered'];
    $post->content = $source['content']['rendered'];
    $post->excerpt = $source['excerpt']['rendered'];
    $post->author = $source['author'];
    $post->featured_media = $source['featured_media'];
    $post->comment_status = $source['comment_status'];
    $post->ping_status = $source['ping_status'];
    $post->sticky = $source['sticky'];
    $post->template = $source['template'];
    $post->format = $source['format'];
    $post->meta = $source['meta'];
    $post->categories = $source['categories'];
    $post->tags = $source['tags'];

    return $post;
  }
}
