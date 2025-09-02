<?php
declare(strict_types=1);

namespace WPDumpSupportTest\Resolver;

use PHPUnit\Framework\TestCase;
use WPDumpSupport\Resolver\MemoryCacheStrategy;
use WPDumpSupport\Object\WPObject;

class MemoryCacheStrategyTest extends TestCase
{
    public function testSetAndGet()
    {
        $strategy = new MemoryCacheStrategy();
        $object = new class extends WPObject { public function __construct() { $this->id = 1; } };

        $strategy->set('test', 1, $object);
        $retrieved = $strategy->get('test', 1);

        $this->assertSame($object, $retrieved);
    }

    public function testGetNonExistent()
    {
        $strategy = new MemoryCacheStrategy();
        $retrieved = $strategy->get('test', 1);

        $this->assertNull($retrieved);
    }

    public function testReset()
    {
        $strategy = new MemoryCacheStrategy();
        $object = new class extends WPObject { public function __construct() { $this->id = 1; } };

        $strategy->set('test', 1, $object);
        $strategy->reset();
        $retrieved = $strategy->get('test', 1);

        $this->assertNull($retrieved);
    }
}