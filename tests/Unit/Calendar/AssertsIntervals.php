<?php

namespace Tests\Unit\Calendar;

trait AssertsIntervals
{
    public function assertSameIntervals($expected, $actual, $message = "")
    {
        $this->assertEquals(count($expected), count($actual), $message);

        $expectedStr = [];
        foreach ($expected as $interval) {
            $expectedStr[] = [
                $interval[0]->toDateTimeString(),
                $interval[1]->toDateTimeString(),
            ];
        }

        $actualStr = [];
        foreach ($actual as $interval) {
            $actualStr[] = [
                $interval[0]->toDateTimeString(),
                $interval[1]->toDateTimeString(),
            ];
        }

        $this->assertEqualsCanonicalizing($expectedStr, $actualStr, $message);
    }
}
