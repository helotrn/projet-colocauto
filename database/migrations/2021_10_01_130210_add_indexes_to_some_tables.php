<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToSomeTables extends Migration
{
    public function up()
    {
        foreach (
            [
                "actions",
                "extensions",
                "handovers",
                "incidents",
                "intentions",
                "payments",
                "pre_payments",
                "takeovers",
            ]
            as $name
        ) {
            Schema::table($name, function (Blueprint $table) {
                $table->index("loan_id");
            });
        }

        foreach (["bikes", "cars", "trailers"] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->index("owner_id");
                $table->index("community_id");
            });
        }

        Schema::table("bill_items", function (Blueprint $table) {
            $table->index("invoice_id");
        });

        Schema::table("communities", function (Blueprint $table) {
            $table->index("parent_id");
        });

        Schema::table("community_user", function (Blueprint $table) {
            $table->index("community_id");
            $table->index("user_id");
        });

        Schema::table("padlocks", function (Blueprint $table) {
            $table->index("loanable_id");
        });

        Schema::table("taggables", function (Blueprint $table) {
            $table->index(["taggable_id", "taggable_type"]);
            $table->index("tag_id");
        });

        foreach (["borrowers", "files", "invoices", "owners"] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->index("user_id");
            });
        }
    }

    public function down()
    {
        foreach (
            [
                "actions",
                "extensions",
                "handovers",
                "incidents",
                "intentions",
                "payments",
                "pre_payments",
                "takeovers",
            ]
            as $name
        ) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropIndex("{$name}_loan_id_index");
            });
        }

        foreach (["bikes", "cars", "trailers"] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropIndex("{$name}_owner_id_index");
                $table->dropIndex("{$name}_community_id_index");
            });
        }

        Schema::table("bill_items", function (Blueprint $table) {
            $table->dropIndex("bill_items_invoice_id_index");
        });

        Schema::table("communities", function (Blueprint $table) {
            $table->dropIndex("communities_parent_id_index");
        });

        Schema::table("community_user", function (Blueprint $table) {
            $table->dropIndex("community_user_community_id_index");
            $table->dropIndex("community_user_user_id_index");
        });

        Schema::table("padlocks", function (Blueprint $table) {
            $table->dropIndex("padlocks_loanable_id_index");
        });

        Schema::table("taggables", function (Blueprint $table) {
            $table->dropIndex(["taggable_id", "taggable_type"]);
            $table->dropIndex("taggables_tag_id_index");
        });

        foreach (["borrowers", "files", "invoices", "owners"] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropIndex("{$name}_user_id_index");
            });
        }
    }
}
