<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderFieldToOauthClients extends Migration
{
    public function up()
    {
        Schema::table("oauth_clients", function (Blueprint $table) {
			if (!Schema::hasColumn('oauth_clients', 'provider')) {
				$table
					->string("provider")
					->nullable()
					->default(null);
			}
        });
    }

    public function down()
    {
		// Do not remove, it should have been there from the beginning
    }
}
