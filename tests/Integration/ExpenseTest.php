<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\Car;
use App\Models\Community;
use App\Models\User;
use App\Models\Expense;
use DateTime;

class ExpenseTest extends TestCase
{
    private static $getExpenseResponseStructure = [
        "id",
        "name",
        "amount",
        "type",
        "user_id",
        "executed_at",
        "locked",
        "loanable_id",
        "loan_id",
        "expense_tag_id",
    ];

    public function testCreateExpenseWithMissingData()
    {
        $community = factory(Community::class)->create();
        $loanable = factory(Car::class)->create(["community_id" => $community->id]);
        $user = factory(User::class)->create();
        $user
            ->communities()
            ->attach($community->id, ["approved_at" => new DateTime()]);

        $response = $this->json("POST", "/api/v1/expenses", []);
        $response->assertStatus(422)->assertJson([
            "errors" => [
                "amount" => ["Le champ montant est obligatoire."],
                "loanable_id" => ["Le champ véhicule est obligatoire."],
                "user_id" => ["Le champ payé par est obligatoire."],
            ],
        ]);
    }

    public function testCreateExpenseWithWrongData()
    {
        $loanable = factory(Car::class)->create();
        $user = factory(User::class)->create();
        $data = [
            "amount" => 10.00,
            "user_id" => $user->id,
            "loanable_id" => $loanable->id,
        ];

        $response = $this->json("POST", "/api/v1/expenses", $data);
        $response->assertStatus(422)->assertJson([
            "errors" => [
                "loanable" => ["L'utilisateur et le véhicule doivent appartenir à la même communauté."],
            ],
        ]);

        $community = factory(Community::class)->create();
        $loanable->community_id = $community->id;
        $loanable->save();

        $response = $this->json("POST", "/api/v1/expenses", $data);
        $response->assertStatus(422)->assertJson([
            "errors" => [
                "loanable" => ["L'utilisateur et le véhicule doivent appartenir à la même communauté."],
            ],
        ]);
    }

    public function testCreateExpense()
    {
        $community = factory(Community::class)->create();
        $loanable = factory(Car::class)->create(["community_id" => $community->id]);
        $user = factory(User::class)->create();
        $user
            ->communities()
            ->attach($community->id, ["approved_at" => new DateTime()]);
        $data = [
            "amount" => 10.00,
            "user_id" => $user->id,
            "loanable_id" => $loanable->id,
        ];
        $response = $this->json("POST", "/api/v1/expenses", $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(static::$getExpenseResponseStructure);
    }

    public function testCreateExpenseWithEmptyLoan()
    {
        $community = factory(Community::class)->create();
        $loanable = factory(Car::class)->create(["community_id" => $community->id]);
        $user = factory(User::class)->create();
        $user
            ->communities()
            ->attach($community->id, ["approved_at" => new DateTime()]);
        $data = [
            "amount" => 10.00,
            "user_id" => $user->id,
            "loanable_id" => $loanable->id,
            "loan_id" => null,
        ];

        $response = $this->json("POST", "/api/v1/expenses", $data);
        $response
            ->assertStatus(201)
            ->assertJsonStructure(static::$getExpenseResponseStructure);
    }

    public function testUpdateExpenseWithEmptyLoan()
    {
        $community = factory(Community::class)->create();
        $loanable = factory(Car::class)->create(["community_id" => $community->id]);
        $user = factory(User::class)->create();
        $user
            ->communities()
            ->attach($community->id, ["approved_at" => new DateTime()]);
        $expense = factory(Expense::class)->create();

        $data = [
            "amount" => $expense->amount,
            "user_id" => $expense->user->id,
            "loanable_id" => $expense->loanable->id,
            "loan_id" => null,
        ];

        $response = $this->json("PUT", "/api/v1/expenses/".$expense->id, $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getExpenseResponseStructure);
    }
}
