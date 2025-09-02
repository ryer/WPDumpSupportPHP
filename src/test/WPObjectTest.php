<?php
declare(strict_types=1);

namespace WPDumpSupportTest\Object;

use PHPUnit\Framework\TestCase;
use WPDumpSupport\Object\WPObject;

class WPObjectTest extends TestCase
{
  public function testProcessSourceWithoutIdThrowsException()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Source array must contain an 'id'.");

    $object = new class extends WPObject {
    };
    $object->processSource(['title' => 'No ID here']);
  }

  public function testProcessSourceSuccessfully()
  {
    $object = new class extends WPObject {
    };
    $source = ['id' => 123, 'title' => 'Test Title'];
    $object->processSource($source);

    $this->assertEquals(123, $object->id);
    $this->assertEquals('Test Title', $object->title);
    $this->assertSame($source, $object->getSource());
  }
}