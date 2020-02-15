<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

class AddDefaultToPaymentStatusEnum extends Migration
{
    public function __construct() {
        Type::addType('enum', StringType::class);
    }

    public function up() {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['in_process', 'canceled', 'completed'])->default('in_process')->change();
        });
    }

    public function down() {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['in_process', 'canceled', 'completed'])->default(null)->change();
        });
    }
}
