<?php

namespace Tests\Unit\Casts;

use MStaack\LaravelPostgis\Geometries\LineString;
use MStaack\LaravelPostgis\Geometries\Point;
use MStaack\LaravelPostgis\Geometries\Polygon;
use App\Casts\PolygonCast;

use Tests\TestCase;

class PolygonCastTest extends TestCase
{
    public function testSetEmpty()
    {
        $cast = new PolygonCast();

        $this->assertNull($cast->set(null, "polygon", null, []));
        $this->assertNull($cast->set(null, "polygon", [], []));
    }

    public function testSetOnePoint()
    {
        $cast = new PolygonCast();

        // "Closed" single-point polygon
        $coordinates = [
            ["55.234567", "-33.456789"],
            ["55.234567", "-33.456789"],
        ];

        $polygon = $cast->set(null, "polygon", $coordinates, []);
        $this->assertInstanceOf(
            \MStaack\LaravelPostgis\Geometries\Polygon::class,
            $polygon
        );
    }

    public function testSetTwoPoints()
    {
        $cast = new PolygonCast();

        $coordinates = [
            ["55.234567", "-33.456789"],
            ["54.321098", "-32.109876"],
            ["55.234567", "-33.456789"],
        ];

        $polygon = $cast->set(null, "polygon", $coordinates, []);
        $this->assertInstanceOf(
            \MStaack\LaravelPostgis\Geometries\Polygon::class,
            $polygon
        );
    }

    public function testSetTriangle()
    {
        $cast = new PolygonCast();

        $coordinates = [
            ["55.234567", "-33.456789"],
            ["56.789012", "-34.567890"],
            ["54.321098", "-32.109876"],
            ["55.234567", "-33.456789"],
        ];

        $polygon = $cast->set(null, "polygon", $coordinates, []);
        $this->assertInstanceOf(
            \MStaack\LaravelPostgis\Geometries\Polygon::class,
            $polygon
        );
    }

    public function testSetString()
    {
        $cast = new PolygonCast();

        $this->assertEquals("", $cast->set(null, "polygon", "", []));

        $this->assertEquals(
            "string",
            $cast->set(null, "polygon", "string", [])
        );
    }

    public function testSetInvalid()
    {
        $this->expectException(\Exception::class);

        $cast = new PolygonCast();

        $cast->set(null, "polygon", -18, []);
    }

    public function testGetEmpty()
    {
        $cast = new PolygonCast();

        $this->assertNull($cast->get(null, "polygon", null, []));
        $this->assertNull($cast->get(null, "polygon", [], []));
    }

    public function testGetTriangle()
    {
        $cast = new PolygonCast();

        $polygon = new Polygon([
            new LineString([
                new Point(55.234567, -33.456789),
                new Point(56.789012, -34.56789),
                new Point(54.321098, -32.109876),
                new Point(55.234567, -33.456789),
            ]),
        ]);

        $expected_coordinates = [
            ["55.234567", "-33.456789"],
            ["56.789012", "-34.567890"],
            ["54.321098", "-32.109876"],
            ["55.234567", "-33.456789"],
        ];

        $coordinates = $cast->get(null, "polygon", $polygon, []);
        $this->assertEquals($expected_coordinates, $coordinates);
    }

    public function testGetArray()
    {
        $cast = new PolygonCast();

        $coordinates = [
            ["55.234567", "-33.456789"],
            ["56.789012", "-34.567890"],
            ["54.321098", "-32.109876"],
            ["55.234567", "-33.456789"],
        ];

        $this->assertEquals(
            $coordinates,
            $cast->get(null, "polygon", $coordinates, [])
        );
    }

    public function testGetString()
    {
        $cast = new PolygonCast();

        $this->assertEquals("", $cast->get(null, "polygon", "", []));

        $this->assertEquals(
            "string",
            $cast->get(null, "polygon", "string", [])
        );
    }
}
