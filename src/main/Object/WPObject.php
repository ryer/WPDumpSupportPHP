<?php
declare(strict_types=1);

namespace WPDumpSupport\Object;

use InvalidArgumentException;
use WPDumpSupport\Resolver\ReferenceResolver;

/**
 * Base class for WordPress objects.
 */
abstract class WPObject
{
  /**
   * @var int
   */
  public $id;

  /**
   * @var array
   */
  protected $source;

  /**
   * Populates object properties from the source array.
   * Subclasses can override this to handle specific data structures.
   *
   * @param array $source
   */
  public function processSource(array $source)
  {
    if (!isset($source['id']))
    {
      throw new InvalidArgumentException("Source array must contain an 'id'.");
    }
    $this->id = $source['id'];
    $this->source = $source;
  }

  public function __get($name)
  {
    if (array_key_exists($name, $this->source))
    {
      $value = $this->source[$name];
      if (is_array($value) && isset($value['rendered']))
      {
        return $value['rendered'];
      }

      return $value;
    }

    return null;
  }

  public function __set($name, $value)
  {
    $this->$name = $value;
  }

  public function __isset($name)
  {
    return isset($this->source[$name]);
  }

  /**
   * A getter for the original source data.
   *
   * @return array
   */
  public function getSource(): array
  {
    return $this->source;
  }

  /**
   * Resolves references to other objects.
   * Subclasses should override this method to resolve their specific references.
   *
   * @param ReferenceResolver $resolver
   */
  public function resolveReferences(ReferenceResolver $resolver)
  {
    // Base implementation does nothing.
  }
}