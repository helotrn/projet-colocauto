<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Order as OrderHelper;
use Carbon\Carbon;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_parseOrderRequestParam()
    {
        $fieldDefs = [
            "name" => ["type" => "string"],
            "departure_at" => ["type" => "carbon"],
            "return_at" => ["type" => "carbon"],
        ];

        $orderParam = "-name,departure_at,-email";

        $this->assertEquals(
            [
                ["field" => "name", "direction" => "desc", "type" => "string"],
                [
                    "field" => "departure_at",
                    "direction" => "asc",
                    "type" => "carbon",
                ],
                ["field" => "email", "direction" => "desc"],
            ],
            OrderHelper::parseOrderRequestParam($orderParam, $fieldDefs)
        );
    }

    public function test_compareStrings()
    {
        $this->assertLessThan(0, OrderHelper::compareStrings("abc", "bcd"));
        $this->assertGreaterThan(0, OrderHelper::compareStrings("bcd", "abc"));
        $this->assertEquals(0, OrderHelper::compareStrings("abc", "abc"));
    }

    public function test_compareCarbon()
    {
        // We're testing the sign of the compare function here, not Carbon...
        $this->assertLessThan(
            0,
            OrderHelper::compareCarbon(
                new Carbon("2022-10-24"),
                new Carbon("2022-10-25")
            )
        );
        $this->assertGreaterThan(
            0,
            OrderHelper::compareCarbon(
                new Carbon("2022-10-25"),
                new Carbon("2022-10-24")
            )
        );
        $this->assertEquals(
            0,
            OrderHelper::compareCarbon(
                new Carbon("2022-10-24"),
                new Carbon("2022-10-24")
            )
        );
    }

    public function test_sortArray()
    {
        $array = [
            [
                "name" => "First",
                "email" => "ccc@locomotion.app",
                "departure_at" => new Carbon("2022-10-26"),
            ],
            [
                "name" => "In the middle",
                "email" => "ccc@locomotion.app",
                "departure_at" => new Carbon("2022-10-25"),
            ],
            [
                "name" => "Last",
                "email" => "bbb@locomotion.app",
                "departure_at" => new Carbon("2022-10-25"),
            ],
            [
                "name" => "In the middle",
                "email" => "aaa@locomotion.app",
                "departure_at" => new Carbon("2022-10-27"),
            ],
        ];

        // Complete order (all fields represented) to ensure repeatable order.
        $orderParams = [
            ["field" => "name", "direction" => "desc", "type" => "string"],
            [
                "field" => "departure_at",
                "direction" => "asc",
                "type" => "carbon",
            ],
            ["field" => "email", "direction" => "desc", "type" => "string"],
        ];

        $array = OrderHelper::sortArray($array, $orderParams);
        $this->assertEquals(
            [
                [
                    "name" => "Last",
                    "email" => "bbb@locomotion.app",
                    "departure_at" => new Carbon("2022-10-25"),
                ],
                [
                    "name" => "In the middle",
                    "email" => "ccc@locomotion.app",
                    "departure_at" => new Carbon("2022-10-25"),
                ],
                [
                    "name" => "In the middle",
                    "email" => "aaa@locomotion.app",
                    "departure_at" => new Carbon("2022-10-27"),
                ],
                [
                    "name" => "First",
                    "email" => "ccc@locomotion.app",
                    "departure_at" => new Carbon("2022-10-26"),
                ],
            ],
            $array
        );

        $orderParams = [
            [
                "field" => "departure_at",
                "direction" => "desc",
                "type" => "carbon",
            ],
            ["field" => "email", "direction" => "asc", "type" => "string"],
            ["field" => "name", "direction" => "desc", "type" => "string"],
        ];

        $array = OrderHelper::sortArray($array, $orderParams);
        $this->assertEquals(
            [
                [
                    "name" => "In the middle",
                    "email" => "aaa@locomotion.app",
                    "departure_at" => new Carbon("2022-10-27"),
                ],
                [
                    "name" => "First",
                    "email" => "ccc@locomotion.app",
                    "departure_at" => new Carbon("2022-10-26"),
                ],
                [
                    "name" => "Last",
                    "email" => "bbb@locomotion.app",
                    "departure_at" => new Carbon("2022-10-25"),
                ],
                [
                    "name" => "In the middle",
                    "email" => "ccc@locomotion.app",
                    "departure_at" => new Carbon("2022-10-25"),
                ],
            ],
            $array
        );

        $orderParams = [
            ["field" => "email", "direction" => "asc", "type" => "string"],
            ["field" => "name", "direction" => "asc", "type" => "string"],
            [
                "field" => "departure_at",
                "direction" => "asc",
                "type" => "carbon",
            ],
        ];

        $array = OrderHelper::sortArray($array, $orderParams);
        $this->assertEquals(
            [
                [
                    "name" => "In the middle",
                    "email" => "aaa@locomotion.app",
                    "departure_at" => new Carbon("2022-10-27"),
                ],
                [
                    "name" => "Last",
                    "email" => "bbb@locomotion.app",
                    "departure_at" => new Carbon("2022-10-25"),
                ],
                [
                    "name" => "First",
                    "email" => "ccc@locomotion.app",
                    "departure_at" => new Carbon("2022-10-26"),
                ],
                [
                    "name" => "In the middle",
                    "email" => "ccc@locomotion.app",
                    "departure_at" => new Carbon("2022-10-25"),
                ],
            ],
            $array
        );

        // Test with some missing "type" attributes. Try comparing as string by default.
        $orderParams = [
            ["field" => "name", "direction" => "desc", "type" => "string"],
            ["field" => "departure_at", "direction" => "asc"],
            ["field" => "email", "direction" => "desc", "type" => "string"],
        ];

        $array = OrderHelper::sortArray($array, $orderParams);
        $this->assertEquals(
            [
                [
                    "name" => "Last",
                    "email" => "bbb@locomotion.app",
                    "departure_at" => new Carbon("2022-10-25"),
                ],
                [
                    "name" => "In the middle",
                    "email" => "ccc@locomotion.app",
                    "departure_at" => new Carbon("2022-10-25"),
                ],
                [
                    "name" => "In the middle",
                    "email" => "aaa@locomotion.app",
                    "departure_at" => new Carbon("2022-10-27"),
                ],
                [
                    "name" => "First",
                    "email" => "ccc@locomotion.app",
                    "departure_at" => new Carbon("2022-10-26"),
                ],
            ],
            $array
        );
    }
}
