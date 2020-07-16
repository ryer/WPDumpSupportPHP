<?php
declare(strict_types=1);

namespace WPDumpSupport;

/**
 * wp-json object
 */
class WPObject
{
  /**
   * @var array
   */
  protected $source;

  /**
   * @var int
   */
  public $id;

  /**
   * @param string $name
   * @return mixed
   */
  public function __get($name)
  {
    return $this->source[$name] ?? null;
  }

  /**
   * @param string $name
   * @param mixed $value
   */
  public function update($name, $value)
  {
    $this->source[$name] = $value;
  }
}
