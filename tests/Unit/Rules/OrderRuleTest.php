<?php

namespace Tests\Unit\Rules;

use App\Rules\OrderRule;

use Tests\TestCase;

class OrderRuleTest extends TestCase
{
    public function testOrderRule()
    {
        $rule = new OrderRule();

        // Single field ascending
        $this->assertTrue($rule->passes("order", "departure_at"));
        // Single field descending
        $this->assertTrue($rule->passes("order", "-departure_at"));
        // Multiple fields
        $this->assertTrue($rule->passes("order", "-name,departure_at,-type"));

        // Some pathological cases
        $this->assertFalse($rule->passes("order", ""));
        $this->assertFalse($rule->passes("order", "-"));
        $this->assertFalse($rule->passes("order", ","));
        $this->assertFalse($rule->passes("order", ".something"));
        $this->assertFalse($rule->passes("order", ",something"));
        $this->assertFalse($rule->passes("order", "-.something"));
        $this->assertFalse($rule->passes("order", "-,something"));
        $this->assertFalse($rule->passes("order", "-name,,-type"));
    }
}
