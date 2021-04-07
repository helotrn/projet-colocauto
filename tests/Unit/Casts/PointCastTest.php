<?php

namespace Tests\Unit\Casts;

use MStaack\LaravelPostgis\Geometries\Point;
use App\Casts\PointCast;

use Tests\TestCase;

class PointCastTest extends TestCase
{
    public function testSetNull() {
        $cast = new PointCast();

        $this->assertNull($cast->set(null, 'point', null, []));
    }

    public function testSetArray() {
        $cast = new PointCast();

        $coordinates = [
          $this->faker->latitude,
          $this->faker->longitude,
        ];

        $point = $cast->set(null, 'point', $coordinates, []);

        $this->assertInstanceOf(\MStaack\LaravelPostgis\Geometries\Point::class, $point);

        $this->assertEquals($coordinates[0], $point->getLat());
        $this->assertEquals($coordinates[1], $point->getLng());
    }

    public function testSetArrayLatLong() {
        $cast = new PointCast();

        $coordinates = [
          'latitude' => $this->faker->latitude,
          'longitude' => $this->faker->longitude,
        ];

        $point = $cast->set(null, 'point', $coordinates, []);

        $this->assertInstanceOf(\MStaack\LaravelPostgis\Geometries\Point::class, $point);

        $this->assertEquals($coordinates['latitude'], $point->getLat());
        $this->assertEquals($coordinates['longitude'], $point->getLng());
    }

    public function testSetPoint() {
        $cast = new PointCast();

        $coordinates = [
          $this->faker->latitude,
          $this->faker->longitude,
        ];

        $point_input = new Point($coordinates[0], $coordinates[1]);

        $point = $cast->set(null, 'point', $point_input, []);
        $this->assertInstanceOf(\MStaack\LaravelPostgis\Geometries\Point::class, $point);

        $this->assertEquals($coordinates[0], $point->getLat());
        $this->assertEquals($coordinates[1], $point->getLng());
    }

    public function testSetString() {
        $cast = new PointCast();

                             // Test a variety of expressions to test the
                             // underlying regexp thoroughly.

        $point = $cast->set(null, 'point', '-37.34567,77.23456', []);
        $this->assertInstanceOf(\MStaack\LaravelPostgis\Geometries\Point::class, $point);
        $this->assertEquals(-37.34567, $point->getLat());
        $this->assertEquals(77.23456, $point->getLng());

        $point = $cast->set(null, 'point', '-37.34567, 77.23456', []);
        $this->assertEquals(-37.34567, $point->getLat());
        $this->assertEquals(77.23456, $point->getLng());

        $point = $cast->set(null, 'point', '-37.34567,  77.23456', []);
        $this->assertEquals(-37.34567, $point->getLat());
        $this->assertEquals(77.23456, $point->getLng());

        $point = $cast->set(null, 'point', '-37.34567 77.23456', []);
        $this->assertEquals(-37.34567, $point->getLat());
        $this->assertEquals(77.23456, $point->getLng());

        $point = $cast->set(null, 'point', '-37.34567  77.23456', []);
        $this->assertEquals(-37.34567, $point->getLat());
        $this->assertEquals(77.23456, $point->getLng());

        $point = $cast->set(null, 'point', '-37,77', []);
        $this->assertEquals(-37, $point->getLat());
        $this->assertEquals(77, $point->getLng());
    }

    public function testSetInvalidEmpty() {
        $this->expectException(\Exception::class);

        $cast = new PointCast();

        $cast->set(null, 'point', [], []);
    }

    public function testSetInvalidString() {
        $this->expectException(\Exception::class);

        $cast = new PointCast();

        $cast->set(null, 'point', '-37.77', []);
    }

    public function testGetNull() {
        $cast = new PointCast();

        $this->assertNull($cast->set(null, 'point', null, []));
    }

    public function testGetArray() {
        $cast = new PointCast();

        $coordinates = ['55.234567', '-33.456789'];

        $this->assertEquals($coordinates, $cast->get(null, 'point', $coordinates, []));
    }

    public function testGetArrayEmpty() {
        $cast = new PointCast();

        $this->assertEquals([], $cast->get(null, 'point', [], []));
    }
}
