<?php

use App\Models\Handover;
use App\Models\Loan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SkipTakeoverForSelfService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $loans = Loan::where("status", "=", "in_process")
            ->whereHas("loanable", function ($q) {
                $q->where("is_self_service", "=", true);
            })
            ->whereHas("takeover", function ($q) {
                $q->where("status", "=", "in_process");
            })
            ->get();

        foreach ($loans as $loan) {
            $loan->takeover->complete()->save();
            if (!$loan->handover) {
                $loan->handover()->save(new Handover());
            }
        }
    }

    public function down()
    {
        // Do nothing.
    }
}
