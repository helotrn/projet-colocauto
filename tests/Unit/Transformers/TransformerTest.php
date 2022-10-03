<?php

namespace Tests\Unit\Transformers;

use App\Transformers\Transformer;
use Tests\TestCase;

class TransformerTest extends TestCase
{
    public function testWrapArrayKeys()
    {
        $transformer = new Transformer();

        $reflectionTransformer = new \ReflectionClass(
            "App\Transformers\Transformer"
        );
        $wrapArrayKeys = $reflectionTransformer->getMethod("wrapArrayKeys");
        $wrapArrayKeys->setAccessible(true);

        // Test with an array.
        $testArrayKeys = $wrapArrayKeys->invokeArgs($transformer, [
            ["field1" => "value1", "field2" => "value2"],
        ]);
        $this->assertEquals(["field1", "field2"], $testArrayKeys);

        // Test with a string, then the value is considered as the key.
        $testArrayKeys = $wrapArrayKeys->invokeArgs($transformer, ["field1"]);
        $this->assertEquals(["field1"], $testArrayKeys);
    }
}
