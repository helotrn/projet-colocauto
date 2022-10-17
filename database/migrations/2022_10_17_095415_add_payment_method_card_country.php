<?php

use App\Facades\Stripe;
use App\Models\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodCardCountry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("payment_methods", function(Blueprint $table){
            $table->char("country", 2)->nullable();
        });

        foreach (PaymentMethod::all() as $paymentMethod) {
            $source = Stripe::getSource($paymentMethod);
            if($source){
                $paymentMethod->country = $source->country;
                $paymentMethod->save();
            }else {
                Log::warning("Could not set country for payment method $paymentMethod->id");
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("payment_methods", function(Blueprint $table){
            $table->dropColumn("country");
        });
    }
}
