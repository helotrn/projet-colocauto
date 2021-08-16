<?php

Route::get("/auth/callback", "AuthController@callback");
Route::get("/auth/google", "AuthController@google");

Route::get("/exports/{any?}", "AssetController@exportFile")
    ->where("any", ".*")
    ->name("assets.export");

Route::get("/borrower/{any?}", "AssetController@borrowerFile")
    ->where("any", ".*")
    ->name("assets.borrower");

Route::get("/communityuser/{any?}", "AssetController@communityUserFile")
    ->where("any", ".*")
    ->name("assets.communityUser");

Route::get("/user/{any?}", "AssetController@userFile")
    ->where("any", ".*")
    ->name("assets.user");

Route::get("/password/reset", "StaticController@app")->name("password.reset");
Route::get("/status", "StaticController@status")->name("status");

// Email test routes
if (env("ENABLE_TEST_ROUTES", false)) {
    Route::get("/test/email/{name}", "TestEmailController@show")->where(
        "name",
        ".*"
    );
}

Route::get("/storage/{any?}", "StaticController@storage")
    ->where("any", ".*")
    ->name("app");

Route::get("/{any?}", "StaticController@app")
    ->where("any", ".*")
    ->name("app");
