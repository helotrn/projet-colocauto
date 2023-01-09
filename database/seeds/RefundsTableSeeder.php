<?php

use App\Models\Refund;
use Illuminate\Database\Seeder;

class RefundsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $refunds = [
            [
                "id" => 1,
                "amount" => 91,
                "executed_at" => "2022-12-22 00:00:00+00",
                "user_id" => 16,
                "credited_user_id" => 14,
            ],
            [
                "id" => 2,
                "amount" => 24,
                "executed_at" => "2022-12-22 00:00:00+00",
                "user_id" => 16,
                "credited_user_id" => 17,
            ],
            [
                "id" => 3,
                "amount" =>3.5,
                "executed_at" => "2022-12-22 00:00:00+00",
                "user_id" => 16,
                "credited_user_id" => 15,
            ],
        ];

        foreach ($refunds as $refund) {
            if (!Refund::where("id", $refund["id"])->exists()) {
                Refund::create($refund);
            } else {
                Refund::where("id", $refund["id"])->update($refund);
            }
        }

        \DB::statement(
            "SELECT setval('refunds_id_seq'::regclass, (SELECT MAX(id) FROM refunds) + 1)"
        );
    }
}
