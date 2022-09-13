<?php

use App\Models\Image;
use App\Models\Pivots\CommunityUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvertCommunityUserProofToFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $proofs = DB::table("images")
            ->where("imageable_type", "=", "App\Models\Pivots\CommunityUser")
            ->where("field", "=", "proof");

        DB::table("files")->insertUsing(
            [
                "fileable_type",
                "fileable_id",
                "path",
                "filename",
                "original_filename",
                "field",
                "filesize",
                "created_at",
                "updated_at",
                "deleted_at",
            ],
            $proofs->select(
                "imageable_type as fileable_type",
                "imageable_id as fileable_id",
                "path",
                "filename",
                "original_filename",
                "field",
                "filesize",
                "created_at",
                "updated_at",
                "deleted_at"
            )
        );

        $proofs->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $proofs = DB::table("files")
            ->where("fileable_type", "=", "App\Models\Pivots\CommunityUser")
            ->where("field", "=", "proof");

        DB::table("images")->insertUsing(
            [
                "imageable_type",
                "imageable_id",
                "path",
                "filename",
                "original_filename",
                "field",
                "filesize",
                "created_at",
                "updated_at",
                "deleted_at",
                "width",
                "height",
            ],
            $proofs->select(
                "fileable_type as imageable_type",
                "fileable_id as imageable_id",
                "path",
                "filename",
                "original_filename",
                "field",
                "filesize",
                "created_at",
                "updated_at",
                "deleted_at",
                DB::raw("-1 as width"),
                DB::raw("-1 as height")
            )
        );

        $proofs->delete();
    }
}
