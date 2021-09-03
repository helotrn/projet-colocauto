<?php

namespace Tests\Unit\Repositories;

use App\Models\BaseModel;
use App\Repositories\RestRepository;
use Tests\TestCase;

class RestRepositoryTest extends TestCase
{
    public function testApplyDateRangeFilter()
    {
        // Empty string should not add condition.
        $query = new BaseModel();
        RestRepository::applyDateRangeFilter("date", "date", "", $query, false);

        $this->assertEquals('select * from "base_models"', $query->toSql());

        // Test bounded interval
        $query = new BaseModel();
        RestRepository::applyDateRangeFilter(
            "date",
            "date",
            "2021-06-01T14:00:00Z@2021-07-01T08:00:00Z",
            $query,
            false
        );

        $this->assertQuerySql(
            'select * from "base_models"' .
                ' where "date" >= \'2021-06-01 10:00:00\'' .
                ' and "date" < \'2021-07-01 04:00:00\'',
            $query
        );

        // Test left-bounded interval with @
        $query = new BaseModel();
        RestRepository::applyDateRangeFilter(
            "date",
            "date",
            "2021-06-01T14:00:00Z@",
            $query,
            false
        );

        $this->assertQuerySql(
            'select * from "base_models" where "date" >= \'2021-06-01 10:00:00\'',
            $query
        );

        // Test left-bounded interval without @
        $query = new BaseModel();
        RestRepository::applyDateRangeFilter(
            "date",
            "date",
            "2021-06-01T14:00:00Z",
            $query,
            false
        );

        $this->assertQuerySql(
            'select * from "base_models" where "date" >= \'2021-06-01 10:00:00\'',
            $query
        );

        // Test right-bounded interval
        $query = new BaseModel();
        RestRepository::applyDateRangeFilter(
            "date",
            "date",
            "@2021-07-01T08:00:00Z",
            $query,
            false
        );

        $this->assertQuerySql(
            'select * from "base_models" where "date" < \'2021-07-01 04:00:00\'',
            $query
        );

        // Test unbounded interval
        $query = new BaseModel();
        RestRepository::applyDateRangeFilter(
            "date",
            "date",
            "@",
            $query,
            false
        );

        $this->assertQuerySql('select * from "base_models"', $query);

        // Test bounded interval with aggregate
        $query = new BaseModel();
        RestRepository::applyDateRangeFilter(
            "date",
            "date",
            "2021-06-01T14:00:00Z@2021-07-01T08:00:00Z",
            $query,
            true
        );

        $this->assertQuerySql(
            'select * from "base_models"' .
                ' having "date" >= \'2021-06-01 10:00:00\'' .
                ' and "date" < \'2021-07-01 04:00:00\'',
            $query
        );
    }

    protected function assertQuerySql(
        $expected_sql,
        $query,
        string $message = ""
    ) {
        $query_str = str_replace(["?"], ['\'%s\''], $query->toSql());
        $query_str = vsprintf($query_str, $query->getBindings());

        $this->assertEquals($expected_sql, $query_str, $message);
    }
}
