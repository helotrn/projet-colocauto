<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\BillItem;
use App\Models\Borrower;
use App\Models\Community;
use App\Models\Extension;
use App\Models\Handover;
use App\Models\Incident;
use App\Models\Intention;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\Takeover;
use App\Models\User;
use Faker\Factory;
use Faker\Generator as Faker;

use Illuminate\Routing\Controller;

class TestEmailController extends Controller
{
    protected $emailBasePath = "emails";

    protected $faker = null;

    public function show($name)
    {
        $viewData = [];
        switch ($name) {
            // Borrower
            case "borrower.approved":
            case "borrower.approved_text":
                $viewData = $this->getBorrowerApprovedTestData();
                break;

            case "borrower.completed":
            case "borrower.completed_text":
                $viewData = $this->getBorrowerCompletedTestData();
                break;

            case "borrower.reviewable":
            case "borrower.reviewable_text":
                $viewData = $this->getBorrowerReviewableTestData();
                break;

            // Invoice
            case "invoice.paid":
            case "invoice.paid_text":
                $viewData = $this->getInvoicePaidTestData();
                break;

            // Loan
            case "loan.canceled":
            case "loan.canceled_text":
                $viewData = $this->getLoanCanceledTestData();
                break;

            case "loan.created":
            case "loan.created_text":
                $viewData = $this->getCommonLoanTestData();
                break;

            case "loan.extension_accepted":
            case "loan.extension_accepted_text":
                $viewData = $this->getLoanExtensionTestData();
                break;

            case "loan.extension_created":
            case "loan.extension_created_text":
                $viewData = $this->getLoanExtensionTestData();
                break;

            case "loan.extension_rejected":
            case "loan.extension_rejected_text":
                $viewData = $this->getLoanExtensionTestData();
                break;

            case "loan.handover_contestation_resolved":
            case "loan.handover_contestation_resolved_text":
                $viewData = $this->getLoanHandoverContestationResolvedTestData();
                break;

            case "loan.handover_contested":
            case "loan.handover_contested_text":
                $viewData = $this->getLoanHandoverContestedTestData();
                break;

            case "loan.handover_reviewable":
            case "loan.handover_reviewable_text":
                $viewData = $this->getLoanHandoverReviewableTestData();
                break;

            case "loan.incident_created":
            case "loan.incident_created_text":
                $viewData = $this->getLoanIncidentCreatedTestData();
                break;

            case "loan.incident_resolved":
            case "loan.incident_resolved_text":
                $viewData = $this->getLoanIncidentResolvedTestData();
                break;

            case "loan.incident_reviewable":
            case "loan.incident_reviewable_text":
                $viewData = $this->getLoanIncidentReviewableTestData();
                break;

            case "loan.intention_accepted":
            case "loan.intention_accepted_text":
                $viewData = $this->getCommonLoanIntentionTestData();
                break;

            case "loan.intention_rejected":
            case "loan.intention_rejected_text":
                $viewData = $this->getCommonLoanIntentionTestData();
                break;

            case "loan.pre_payment_missing":
            case "loan.pre_payment_missing_text":
                $viewData = $this->getLoanPrePaymentMissingTestData();
                break;

            case "loan.takeover_contestation_resolved":
            case "loan.takeover_contestation_resolved_text":
                $viewData = $this->getLoanTakeoverContestationResolvedTestData();
                break;

            case "loan.takeover_contested":
            case "loan.takeover_contested_text":
                $viewData = $this->getLoanTakeoverContestedTestData();
                break;

            case "loan.takeover_reviewable":
            case "loan.takeover_reviewable_text":
                $viewData = $this->getLoanTakeoverReviewableTestData();
                break;

            case "loan.upcoming":
            case "loan.upcoming_text":
                $viewData = $this->getLoanUpcomingTestData();
                break;

            // Loanable
            case "loanable.created":
            case "loanable.created_text":
                $viewData = $this->getLoanableCreatedTestData();
                break;

            case "loanable.reviewable":
            case "loanable.reviewable_text":
                $viewData = $this->getLoanableReviewableTestData();
                break;

            // Password
            case "password.request":
            case "password.request_text":
                $viewData = $this->getPasswordRequestTestData();
                break;

            case "registration.rejected":
            case "registration.rejected_text":
                $viewData = $this->getRegistrationRejectedTestData();
                break;

            case "registration.reviewable":
            case "registration.reviewable_text":
                $viewData = $this->getRegistrationReviewableTestData();
                break;

            case "registration.stalled":
            case "registration.stalled_text":
                $viewData = $this->getCommonTestData();
                break;

            // User
            case "user.claimed_balance":
            case "user.claimed_balance_text":
                $viewData = $this->getUserClaimedBalanceTestData();
                break;

            default:
                break;
        }

        return view($this->emailBasePath . "." . $name, $viewData);
    }

    protected function getFaker()
    {
        if ($this->faker === null) {
            $this->faker = Factory::create("fr_CA");
        }

        return $this->faker;
    }

    protected function getCommonTestData()
    {
        $faker = $this->getFaker();

        $testData = [
            "title" => $faker->sentence,
        ];

        return $testData;
    }

    protected function getCommonLoanTestData()
    {
        $faker = $this->getFaker();

        $community = factory(Community::class)->make();

        $borrowerUser = factory(User::class)->make();
        $borrowerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $borrower = factory(Borrower::class)->make();
        $borrower->user = $borrowerUser;

        $ownerUser = factory(User::class)->make();
        $ownerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $owner = factory(Owner::class)->make();
        $owner->user = $ownerUser;

        $loanable = factory(Bike::class)->make();
        $loanable->owner = $owner;

        $loan = factory(Loan::class)->make();
        $loan->id = (int) $faker->numberBetween(1, 1234);
        $loan->owner = $owner;
        $loan->borrower = $borrower;
        $loan->loanable = $loanable;

        $testData = array_merge($this->getCommonTestData(), [
            "borrower_user" => $borrowerUser,
            "borrower" => $borrower,
            "owner_user" => $ownerUser,
            "owner" => $owner,
            "loanable" => $loanable,
            "loan" => $loan,
        ]);

        return $testData;
    }

    protected function getBorrowerApprovedTestData()
    {
        $testData = array_merge($this->getCommonTestData(), [
            "user" => factory(User::class)->make(),
        ]);

        return $testData;
    }

    protected function getBorrowerCompletedTestData()
    {
        $testData = array_merge($this->getCommonTestData(), [
            "user" => factory(User::class)->make(),
        ]);

        return $testData;
    }

    protected function getBorrowerReviewableTestData()
    {
        $testData = array_merge($this->getCommonTestData(), [
            "user" => factory(User::class)->make(),
            "communities" => [
                factory(Community::class)->make(),
                factory(Community::class)->make(),
            ],
        ]);

        return $testData;
    }

    protected function getInvoicePaidTestData()
    {
        $faker = $this->getFaker();

        $invoice = factory(Invoice::class)->make();
        $invoice->id = (int) $faker->numberBetween(1, 1234);

        $invoice->bill_items = [
            factory(BillItem::class)->make(),
            factory(BillItem::class)->make(),
            factory(BillItem::class)->make(),
            factory(BillItem::class)->make(),
        ];

        // Compute real totals.
        $total = 0;
        foreach ($invoice->bill_items as $bill_item) {
            $total += $bill_item->amount;
        }

        // Compute taxes.
        $invoice["total"] = $total;
        $invoice["total_tps"] = 0.05 * $total;
        $invoice["total_tvq"] = 0.09975 * $total;
        $invoice["total_with_taxes"] = 1.14975 * $total;

        $testData = array_merge($this->getCommonTestData(), [
            "text" => $faker->sentence(),
            "user" => factory(User::class)->make(),
            "invoice" => $invoice,
        ]);

        return $testData;
    }

    protected function getLoanCanceledTestData()
    {
        $testData = $this->getCommonLoanTestData();

        $testData["receiver"] = $testData["borrower_user"];
        $testData["sender"] = $testData["owner_user"];

        return $testData;
    }

    protected function getLoanExtensionTestData()
    {
        $extension = factory(Extension::class)->make();

        $testData = array_merge($this->getCommonLoanTestData(), [
            "extension" => $extension,
        ]);

        return $testData;
    }

    protected function getLoanHandoverContestationResolvedTestData()
    {
        $testData = $this->getCommonLoanTestData();

        $testData["receiver"] = $testData["borrower_user"];
        $testData["admin"] = factory(User::class)->make();

        return $testData;
    }

    protected function getLoanHandoverContestedTestData()
    {
        $faker = $this->getFaker();

        $testData = $this->getCommonLoanTestData();

        $handover = factory(Handover::class)->make();
        $handover->comments_on_contestation = $faker->optional()->paragraph;

        $testData["receiver"] = $testData["borrower_user"];
        $testData["caller"] = $testData["owner_user"];
        $testData["handover"] = $handover;

        return $testData;
    }

    protected function getLoanHandoverReviewableTestData()
    {
        $faker = $this->getFaker();

        $testData = $this->getCommonLoanTestData();

        $handover = factory(Handover::class)->make();
        $handover->comments_on_contestation = $faker->optional()->paragraph;

        $testData["caller"] = $testData["owner_user"];
        $testData["handover"] = $handover;

        return $testData;
    }

    protected function getLoanIncidentCreatedTestData()
    {
        $testData = array_merge($this->getCommonLoanTestData(), [
            "incident" => factory(Incident::class)->make(),
        ]);

        return $testData;
    }

    protected function getLoanIncidentResolvedTestData()
    {
        $testData = $this->getCommonLoanTestData();

        $testData["target"] = $testData["owner"];
        $testData["incident"] = factory(Incident::class)->make();

        return $testData;
    }

    protected function getLoanIncidentReviewableTestData()
    {
        $testData = array_merge($this->getCommonLoanTestData(), [
            "incident" => factory(Incident::class)->make(),
        ]);

        return $testData;
    }

    protected function getCommonLoanIntentionTestData()
    {
        $testData = array_merge($this->getCommonLoanTestData(), [
            "intention" => factory(Intention::class)->make(),
        ]);

        return $testData;
    }

    protected function getLoanPrePaymentMissingTestData()
    {
        $testData = $this->getCommonLoanTestData();

        $testData["user"] = $testData["borrower_user"];

        return $testData;
    }

    protected function getLoanTakeoverContestationResolvedTestData()
    {
        $testData = $this->getCommonLoanTestData();

        $testData["receiver"] = $testData["borrower_user"];
        $testData["admin"] = factory(User::class)->make();

        return $testData;
    }

    protected function getLoanTakeoverContestedTestData()
    {
        $faker = $this->getFaker();

        $takeover = factory(Takeover::class)->make();
        $takeover->comments_on_contestation = $faker->optional()->paragraph;

        $testData = $this->getCommonLoanTestData();

        $testData["receiver"] = $testData["borrower_user"];
        $testData["caller"] = $testData["owner_user"];
        $testData["takeover"] = $takeover;

        return $testData;
    }

    protected function getLoanTakeoverReviewableTestData()
    {
        $faker = $this->getFaker();

        $testData = $this->getCommonLoanTestData();

        $takeover = factory(Takeover::class)->make();
        $takeover->comments_on_contestation = $faker->optional()->paragraph;

        $testData["caller"] = $testData["owner_user"];
        $testData["takeover"] = $takeover;

        return $testData;
    }

    protected function getLoanUpcomingTestData()
    {
        $testData = $this->getCommonLoanTestData();

        $testData["user"] = $testData["borrower_user"];

        return $testData;
    }

    protected function getLoanableCreatedTestData()
    {
        $testData = array_merge($this->getCommonTestData(), [
            "user" => factory(User::class)->make(),
            "loanable" => factory(Bike::class)->make(),
        ]);

        return $testData;
    }

    protected function getLoanableReviewableTestData()
    {
        $faker = $this->getFaker();

        $loanable = factory(Bike::class)->make();
        $loanable->id = (int) $faker->numberBetween(1, 1234);

        $testData = array_merge($this->getCommonTestData(), [
            "community" => factory(Community::class)->make(),
            "user" => factory(User::class)->make(),
            "loanable" => $loanable,
        ]);

        return $testData;
    }

    protected function getPasswordRequestTestData()
    {
        $faker = $this->getFaker();

        $testData = array_merge($this->getCommonTestData(), [
            "expiration" => (int) $faker->numberBetween(1, 12),
            "route" => url(
                "/password/reset/" .
                    "?token=" .
                    $faker->shuffle(
                        "b3eb295e335b70c2dfab62d2c58c674d" .
                            "a63dc88bac039d6e1471d81c89842987"
                    ) .
                    "&email=" .
                    $faker->email
            ),
        ]);

        return $testData;
    }

    protected function getRegistrationRejectedTestData()
    {
        $testData = array_merge($this->getCommonTestData(), [
            "user" => factory(User::class)->make(),
        ]);

        return $testData;
    }

    protected function getRegistrationReviewableTestData()
    {
        $faker = $this->getFaker();

        $community = factory(Community::class)->make();
        $community->id = (int) $faker->numberBetween(1, 1234);

        $testData = array_merge($this->getCommonTestData(), [
            "user" => factory(User::class)->make(),
            "community" => $community,
        ]);

        return $testData;
    }

    protected function getUserClaimedBalanceTestData()
    {
        $faker = $this->getFaker();

        $user = factory(User::class)->make();
        $user->id = (int) $faker->numberBetween(1, 1234);

        $testData = array_merge($this->getCommonTestData(), [
            "user" => $user,
        ]);

        return $testData;
    }
}
