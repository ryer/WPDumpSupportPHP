<?php
declare(strict_types=1);

namespace WPDumpSupport;

use Exception;

/**
 * page
 * @property int|WPUser $author
 * @property int|WPMedia $featured_media
 */
class WPPage extends WPObject
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
  public $template;
  public $meta;

  /**
   * @param array $source
   * @return WPPage
   */
  public static function processSource(array $source)
  {
    if (!isset($source['id']) or !isset($source['type']) or $source['type'] !== 'page')
    {
      throw new Exception("unexpected source");
    }

    $page = new WPPage();
    $page->id = $source['id'];
    $page->date = $source['date'];
    $page->date_gmt = $source['date_gmt'];
    $page->guid = $source['guid']['rendered'];
    $page->modified = $source['modified'];
    $page->modified_gmt = $source['modified_gmt'];
    $page->slug = $source['slug'];
    $page->status = $source['status'];
    $page->type = $source['type'];
    $page->link = $source['link'];
    $page->title = $source['title']['rendered'];
    $page->content = $source['content']['rendered'];
    $page->excerpt = $source['excerpt']['rendered'];
    $page->author = $source['author'];
    $page->featured_media = $source['featured_media'];
    $page->comment_status = $source['comment_status'];
    $page->ping_status = $source['ping_status'];
    $page->template = $source['template'];
    $page->meta = $source['meta'];

    return $page;
  }
}
