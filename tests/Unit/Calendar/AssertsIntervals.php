<?php

namespace Tests\Unit\Calendar;

trait AssertsIntervals
{
    public function assertSameIntervals($expected, $actual, $message = "")
    {
        $this->assertEquals(count($expected), count($actual), $message);

        foreach ($expected as $i => $interval) {
            $this->assertTrue($interval[0]->equalTo($actual[$i][0]), $message);
            $this->assertTrue($interval[1]->equalTo($actual[$i][1]), $message);
        }
    }
}
