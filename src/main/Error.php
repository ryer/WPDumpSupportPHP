<?php
declare(strict_types=1);

namespace WPDumpSupport;

class Error
{
  public const NOTICE = 'notice';
  public const WARNING = 'warning';
  public const ERROR = 'error';

  /**
   * @var string
   */
  private $level;

  /**
   * @var string
   */
  private $message;

  /**
   * @param string $level
   * @param string $message
   */
  public function __construct(string $level, string $message)
  {
    $this->level = $level;
    $this->message = $message;
  }

  public function __toString()
  {
    return sprintf('[%s] %s', $this->level, $this->message);
  }

  /**
   * @return string
   */
  public function getLevel(): string
  {
    return $this->level;
  }

  /**
   * @return string
   */
  public function getMessage(): string
  {
    return $this->message;
  }
}