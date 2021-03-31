<?php

namespace Tests\Unit\Rules;

use App\Rules\Polygon;

use Tests\TestCase;

class PolygonTest extends TestCase
{
    public function testPassesEmpty() {
        $rule = new Polygon();

        $this->assertTrue($rule->passes('polygon', null));
        $this->assertTrue($rule->passes('polygon', []));
    }


    public function testPassesOnePoint() {
        $rule = new Polygon();

                             // "Open" single-point polygon is not valid.
        $coordinates = [
          ['55.234567', '-33.456789'],
        ];

        $this->assertFalse($rule->passes('polygon', $coordinates));

                             // "Closed" single-point polygon is valid.
        $coordinates = [
          ['55.234567', '-33.456789'],
          ['55.234567', '-33.456789'],
        ];

        $this->assertTrue($rule->passes('polygon', $coordinates));
    }

    public function testPassesTriangle() {
        $rule = new Polygon();

                             // "Open" triangle is not valid.
        $coordinates = [
          ['55.234567', '-33.456789'],
          ['56.789012', '-34.567890'],
          ['54.321098', '-32.109876'],
        ];

        $this->assertFalse($rule->passes('polygon', $coordinates));

                             // "Closed" triangle is valid.
        $coordinates = [
          ['55.234567', '-33.456789'],
          ['56.789012', '-34.567890'],
          ['54.321098', '-32.109876'],
          ['55.234567', '-33.456789'],
        ];

        $this->assertTrue($rule->passes('polygon', $coordinates));
    }

    public function testPassesInvalidTypes() {
        $rule = new Polygon();

                             // Anything not an array or null should
                             // not be accepted. Test just a few
                             // here.

                             // Numbers not accepted.
        $this->assertFalse($rule->passes('polygon', 2.3456));
                             // Strings not accepted.
        $this->assertFalse($rule->passes('polygon', "A string"));
    }

    public function testMessage() {
        $rule = new Polygon();

        $this->assertNotEmpty($rule->message());
    }
}
