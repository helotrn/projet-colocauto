<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OauthIdString extends Migration
{
    public function up() {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->string('client_id', 100)->change();
        });
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('id', 100)->change();
        });
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->string('client_id', 100)->change();
        });
        Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
            $table->string('client_id', 100)->change();
        });
    }

    public function down() {
        // Don't rollback
    }
}
