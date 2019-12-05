<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable extends Migration
{
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->default('');
            $table->string('google_id')->nullable();
            $table->text('description')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->default('');
            $table->string('postal_code')->default('');
            $table->string('phone')->default('');
            $table->boolean('is_smart_phone')->default(false);
            $table->string('other_phone')->default('');
            $table->date('approved_at')->nullable();
        });
    }

    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_name');
            $table->dropColumn('google_id');
            $table->dropColumn('description');
            $table->dropColumn('date_of_birth');
            $table->dropColumn('address');
            $table->dropColumn('postal_code');
            $table->dropColumn('phone');
            $table->dropColumn('is_smart_phone');
            $table->dropColumn('other_phone');
            $table->dropColumn('approved_at');
        });
    }
}
