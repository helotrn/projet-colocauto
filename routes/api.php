<?php

use App\Http\Controllers\LoanableController;
use App\Http\Controllers\UserController;
use Molotov\Utils\RouteHelper;

Route::prefix("v1")->group(function () {
    Route::get("/", "StaticController@blank");
    Route::get("/status", "StaticController@status");
    Route::get("/stats", "StaticController@stats");

    Route::post("auth/login", "AuthController@login");
    Route::post("auth/register", "AuthController@register");
    Route::post("auth/password/request", "AuthController@passwordRequest");
    Route::post("auth/password/reset", "AuthController@passwordReset");
    Route::get(
        "auth/password/mandate/{userId}",
        "AuthController@mandate"
    )->middleware("auth:api");

    Route::middleware(["auth:api", "transaction"])->group(function () {
        Route::prefix("auth")->group(function () {
            Route::get("user", "AuthController@retrieveUser");
            Route::put("user", "AuthController@updateUser");
            Route::put("user/submit", "AuthController@submitUser");
            Route::get("user/balance", "AuthController@getUserBalance");
            Route::put("user/balance", "AuthController@addToUserBalance");
            Route::put("user/claim", "AuthController@claimUserBalance");
            Route::put("logout", "AuthController@logout");
        });

        Route::post("acceptConditions", [
            UserController::class,
            "acceptConditions",
        ]);

        foreach (
            [
                "action",
                "bike",
                "car",
                "community",
                "file",
                "image",
                "invoice",
                "loan",
                "loanable",
                "padlock",
                "payment_method",
                "tag",
                "trailer",
                "user",
                "invitation",
                "expense",
                "expense_tag",
                "refund",
            ]
            as $entity
        ) {
            RouteHelper::resource($entity);
        }

        Route::get("users/{me}", "UserController@retrieve")->where("me", "me");

        Route::put("users/send/{type}", "UserController@sendEmail");

        RouteHelper::retrieve("borrower");
        RouteHelper::index("borrower");
        RouteHelper::retrieve("owner");
        RouteHelper::index("owner");

        Route::post("users/{user_id}/email", "UserController@updateEmail");
        Route::post(
            "users/{user_id}/password",
            "UserController@updatePassword"
        );
        Route::get(
            "users/{user_id}/communities",
            "UserController@indexCommunities"
        );
        Route::get(
            "users/{user_id}/communities/{community_id}",
            "UserController@retrieveCommunity"
        );
        Route::put(
            "users/{user_id}/communities/{community_id}",
            "UserController@createCommunityUser"
        );
        Route::delete(
            "users/{user_id}/communities/{community_id}",
            "UserController@deleteCommunityUser"
        );

        Route::get(
            "/users/{user_id}/borrower",
            "UserController@retrieveBorrower"
        );
        Route::put(
            "/users/{user_id}/borrower/approve",
            "UserController@approveBorrower"
        );
        Route::put(
            "/users/{user_id}/borrower/suspend",
            "UserController@suspendBorrower"
        );
        Route::delete(
            "/users/{user_id}/borrower/suspend",
            "UserController@unsuspendBorrower"
        );

        RouteHelper::subresource("user", "tag");

        RouteHelper::subresource("community", "user");

        Route::get(
            "communities/{community_id}/users/{user_id}/tags",
            "CommunityController@indexCommunityUserTags"
        );
        Route::put(
            "communities/{community_id}/users/{user_id}/tags/{tag_id}",
            "CommunityController@updateCommunityUserTags"
        );
        Route::delete(
            "communities/{community_id}/users/{user_id}/tags/{tag_id}",
            "CommunityController@destroyCommunityUserTags"
        );

        Route::get(
            "communities/{community_id}/admins",
            "CommunityController@listAdmins"
        );
        Route::post(
            "communities/{community_id}/admins",
            "CommunityController@createAdmins"
        );
        Route::delete(
            "communities/{community_id}/admins/{user_id}",
            "CommunityController@destroyAdmins"
        );

        Route::get(
            "/loans/{loan_id}/borrower",
            "LoanController@retrieveBorrower"
        );
        Route::put("/loans/{loan_id}/cancel", "LoanController@cancel");

        Route::post("/loans/{loan_id}/actions", "LoanController@createAction");
        Route::get(
            "/loans/{loan_id}/actions/{action_id}",
            "LoanController@retrieveAction"
        );
        Route::put(
            "/loans/{loan_id}/actions/{action_id}/complete",
            "ActionController@complete"
        );
        Route::put(
            "/loans/{loan_id}/intention/{action_id}/complete",
            "IntentionController@complete"
        );
        Route::put(
            "/loans/{loan_id}/actions/{action_id}/update_mileage",
            "ActionController@updateMileage"
        );
        Route::put(
            "/loans/{loan_id}/actions/{action_id}/cancel",
            "ActionController@cancel"
        );
        Route::put(
            "/loans/{loan_id}/actions/{action_id}/reject",
            "ActionController@reject"
        );
        Route::get(
            "/loans/{loan_id}/isavailable",
            "LoanController@isAvailable"
        );
        Route::put(
            "/loans/{loan_id}/validate",
            "LoanController@validateInformation"
        );

        Route::put(
            "/pricings/{id}/evaluate",
            "PricingController@evaluate"
        )->name("pricings.evaluate");

        Route::get("/loanables/{id}/test", "LoanableController@test")->where(
            "id",
            "[0-9]+"
        );
        Route::get("/loanables/search", "LoanableController@search");
        Route::get("/loanables/list", "LoanableController@list");
        Route::get(
            "/loanables/{loanable_id}/availability",
            "LoanableController@availability"
        )->where("loanable_id", "[0-9]+");
        Route::get(
            "/loanables/{loanable_id}/events",
            "LoanableController@events"
        )->where("loanable_id", "[0-9]+");
        Route::get(
            "/loanables/{loanable_id}/loans/{loan_id}/next",
            "LoanableController@retrieveNextLoan"
        )
            ->where("loanable_id", "[0-9]+")
            ->where("loan_id", "[0-9]+");

        Route::put("/loanables/{loanable_id}/coowners", [
            LoanableController::class,
            "addCoowner",
        ])->where("loanable_id", "[0-9]+");

        Route::delete("/loanables/{loanable_id}/coowners", [
            LoanableController::class,
            "removeCoowner",
        ])->where("loanable_id", "[0-9]+");

        Route::put("/loanables/{loanable_id}/coowners/{coowner_id}", [
            LoanableController::class,
            "updateCoowner",
        ])->where("loanable_id", "[0-9]+")
        ->where("coowner_id", "[0-9]+");

        RouteHelper::subresource("loanable", "loan", null, [
            "create",
            "update",
            "destroy",
            "restore",
        ]);

        Route::get("/loans/dashboard", "LoanController@dashboard");

        Route::get(
            "communities/{community_id}/balance",
            "CommunityController@getExpensesBalance"
        );

        Route::post("/invitations/{invitation_id}/resend", "InvitationController@resend");
        Route::post("/invitations/accept", "InvitationController@accept");
    });

    Route::get(
        "/scheduler/noke/sync/locks/{appKey?}",
        "SchedulerController@nokeSyncLocks"
    )->where("appKey", ".*");
    Route::get(
        "/scheduler/noke/sync/users/{appKey?}",
        "SchedulerController@nokeSyncUsers"
    )->where("appKey", ".*");
    Route::get(
        "/scheduler/noke/sync/loans/{appKey?}",
        "SchedulerController@nokeSyncLoans"
    )->where("appKey", ".*");
    Route::get(
        "/scheduler/actions/complete/{appKey?}",
        "SchedulerController@actionsComplete"
    )->where("appKey", ".*");
    Route::get(
        "/scheduler/email/loan/upcoming/{appKey?}",
        "SchedulerController@emailLoanUpcoming"
    )->where("appKey", ".*");
    Route::get(
        "/scheduler/email/loan/prepayement/{appKey?}",
        "SchedulerController@emailPrePayement"
    )->where("appKey", ".*");

    Route::get("/{any?}", "StaticController@notFound")->where("any", ".*");
});
