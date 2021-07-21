<?php

namespace Tests\Unit\Models;

use App\Models\Car;
use App\Models\Loan;
use App\Models\Pricing;
use Illuminate\Support\Str;
use Tests\TestCase;

class PricingTest extends TestCase
{
    public function testRuleEvaluationWithMandatoryVariables()
    {
        $pricing = new Pricing();

        $pricing->rule = '$KM * 10 + $MINUTES * 2';

        $this->assertEquals(16, $pricing->evaluateRule(1, 3));
        $this->assertEquals(0, $pricing->evaluateRule(0, 0));
        $this->assertEquals(10, $pricing->evaluateRule(1, 0));
    }

    public function testRuleEvaluationSkipsSyntaxErrors()
    {
        $pricing = new Pricing();

        $pricing->rule = <<<RULE
\$KM * 10 + * + \$MINUTES * 2
12345
RULE;

        $this->assertEquals(12345, $pricing->evaluateRule(1, 3));
    }

    public function testRuleEvaluationReturnsNullIfNoAnswerBecauseOfSyntaxError()
    {
        $pricing = new Pricing();

        $pricing->rule = <<<RULE
\$KM * 10 + * + \$MINUTES * 2
RULE;

        $this->assertEquals(null, $pricing->evaluateRule(1, 3));
    }

    public function testRuleEvaluationReturnsNullIfNoMatch()
    {
        $pricing = new Pricing();

        $pricing->rule = <<<RULE
SI \$KM > 10 ALORS \$KM * 10 + \$MINUTES * 2
RULE;

        $this->assertEquals(null, $pricing->evaluateRule(1, 3));
    }

    public function testRuleEvaluationWithConditions()
    {
        $pricing = new Pricing();

        $pricing->rule = <<<RULE
SI \$KM > 20 ALORS 1
SI \$KM > 10 ALORS 2
3
RULE;

        $this->assertEquals(1, $pricing->evaluateRule(21, 0));
        $this->assertEquals(2, $pricing->evaluateRule(11, 0));
        $this->assertEquals(3, $pricing->evaluateRule(1, 0));
    }

    public function testRuleEvaluationConditionsOrder()
    {
        $pricing = new Pricing();

        $pricing->rule = <<<RULE
SI \$KM > 20 ALORS 1
SI \$KM > 10 ALORS 2
SI \$MINUTES > 20 ALORS 3
SI \$MINUTES > 10 ALORS 4
5
RULE;

        $this->assertEquals(1, $pricing->evaluateRule(21, 0));
        $this->assertEquals(2, $pricing->evaluateRule(11, 0));
        $this->assertEquals(3, $pricing->evaluateRule(0, 21));
        $this->assertEquals(4, $pricing->evaluateRule(0, 11));
        $this->assertEquals(5, $pricing->evaluateRule(0, 0));

        $this->assertEquals(2, $pricing->evaluateRule(11, 21));
    }

    public function testRuleEvaluationOnObjectVariable()
    {
        $pricing = new Pricing();

        $pricing->rule = <<<RULE
SI \$OBJET.pricing_category == 'small' ALORS 10
\$OBJET.year_of_circulation
RULE;

        $car = new Car();
        $car->year_of_circulation = 1235;

        $car->pricing_category = "small";
        $this->assertEquals(10, $pricing->evaluateRule(0, 0, $car));

        $car->pricing_category = "large";
        $this->assertEquals(1235, $pricing->evaluateRule(0, 0, $car));
    }

    public function testRuleEvalationOnLoanVariables()
    {
        $loan = new Loan();
        $loan->duration_in_minutes = 12 * 60;
        $loan->departure_at = new \DateTime("2020-01-01 16:00:00");

        $car = new Car();
        $car->pricing_category = "small";

        $pricing = new Pricing();
        $pricing->rule = <<<RULE
SI \$OBJET.pricing_category == 'small' && \$EMPRUNT.days > 1 ALORS 1
SI \$OBJET.pricing_category == 'small' ALORS 2
SI \$OBJET.pricing_category == 'large' && \$EMPRUNT.days > 1 ALORS 3
SI \$OBJET.pricing_category == 'large' ALORS 4
4321
RULE;

        $this->assertEquals(1, $pricing->evaluateRule(0, 0, $car, $loan));

        $car->pricing_category = "large";
        $this->assertEquals(3, $pricing->evaluateRule(0, 0, $car, $loan));

        $loan->duration_in_minutes = 60;
        $this->assertEquals(4, $pricing->evaluateRule(0, 0, $car, $loan));

        $car->pricing_category = "small";
        $this->assertEquals(2, $pricing->evaluateRule(0, 0, $car, $loan));

        $car->pricing_category = "asfads"; // Invalid but we're just testing the fallback
        $this->assertEquals(4321, $pricing->evaluateRule(0, 0, $car, $loan));
    }

    public function testRuleEvaluationMethods()
    {
        $pricing = new Pricing();

        // PLANCHER
        $pricing->rule = 'PLANCHER($KM)';
        $this->assertEquals(1, $pricing->evaluateRule(1.589, 0));
        $this->assertEquals(2, $pricing->evaluateRule(2.122, 0));

        // PLAFOND
        $pricing->rule = 'PLAFOND($KM)';
        $this->assertEquals(2, $pricing->evaluateRule(1.589, 0));
        $this->assertEquals(3, $pricing->evaluateRule(2.122, 0));

        // ARRONDI
        $pricing->rule = 'ARRONDI($KM)';
        $this->assertEquals(2, $pricing->evaluateRule(1.589, 0));
        $this->assertEquals(2, $pricing->evaluateRule(2.122, 0));

        // DOLLARS
        $pricing->rule = 'DOLLARS($KM)';
        $this->assertEquals(1.59, $pricing->evaluateRule(1.589, 0));
        $this->assertEquals(2.12, $pricing->evaluateRule(2.122, 0));
    }

    public function testRuleEvaluationMayReturnArray()
    {
        $pricing = new Pricing();
        $pricing->rule = "[1, 2]";
        $this->assertEquals([1, 2], $pricing->evaluateRule(0, 0));
    }

    public function testRuleEvaluationMustReturnTupleOfTwoOrNumber()
    {
        $pricing = new Pricing();

        $pricing->rule = '\'test\'';
        $this->assertEquals(null, $pricing->evaluateRule(0, 0));

        $pricing->rule = "1";
        $this->assertEquals(1, $pricing->evaluateRule(0, 0));

        $pricing->rule = "[1]";
        $this->assertEquals(null, $pricing->evaluateRule(0, 0));

        $pricing->rule = "[1, 2]";
        $this->assertEquals([1, 2], $pricing->evaluateRule(0, 0));

        $pricing->rule = "[1, 'test']";
        $this->assertEquals(null, $pricing->evaluateRule(0, 0));

        $pricing->rule = "[1, 2, 3]";
        $this->assertEquals(null, $pricing->evaluateRule(0, 0));
    }

    public function testRuleEvaluationWithLogicalOperators()
    {
        $pricing = new Pricing();

        // ET
        $pricing->rule = 'SI $KM == 1 ET $MINUTES == 2 ALORS 1234';
        $this->assertEquals(1234, $pricing->evaluateRule(1, 2));
        $this->assertEquals(null, $pricing->evaluateRule(2, 2));

        // OU
        $pricing->rule = 'SI $KM == 1 OU $MINUTES == 2 ALORS 1234';
        $this->assertEquals(1234, $pricing->evaluateRule(1, 1));
        $this->assertEquals(1234, $pricing->evaluateRule(2, 2));
        $this->assertEquals(null, $pricing->evaluateRule(3, 3));
    }

    public function testRuleEvaluationWithArrayOperators()
    {
        $pricing = new Pricing();

        // DANS
        $pricing->rule = 'SI $KM DANS [1] ALORS 1234';
        $this->assertEquals(1234, $pricing->evaluateRule(1, 0));
        $this->assertEquals(null, $pricing->evaluateRule(2, 0));

        // PAS DANS
        $pricing->rule = 'SI $KM PAS DANS [1] ALORS 1234';
        $this->assertEquals(null, $pricing->evaluateRule(1, 0));
        $this->assertEquals(1234, $pricing->evaluateRule(2, 0));
    }

    public function testRuleEvaluationRanges()
    {
        $pricing = new Pricing();

        $pricing->rule = 'SI $KM DANS 1..3 ALORS 1234';
        $this->assertEquals(null, $pricing->evaluateRule(0, 0));
        $this->assertEquals(1234, $pricing->evaluateRule(1, 0));
        $this->assertEquals(1234, $pricing->evaluateRule(2, 0));
        $this->assertEquals(1234, $pricing->evaluateRule(3, 0));
        $this->assertEquals(null, $pricing->evaluateRule(4, 0));
    }

    public function testRuleEvaluationDateProperties()
    {
        $loan = new Loan();
        $date = new \Carbon\Carbon("2020-01-01 16:00:00");
        $loan->duration_in_minutes = 12 * 60;
        $loan->departure_at = $date;

        $pricing = new Pricing();

        foreach (
            ["year", "month", "day", "hour", "minute", "day_of_year"]
            as $key
        ) {
            $pricing->rule = "\$EMPRUNT.start.$key";
            $camelKey = Str::camel($key);
            $this->assertEquals(
                $date->{$camelKey},
                $pricing->evaluateRule(0, 0, null, $loan)
            );
        }
    }
}
