<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\User;
use Faker\Factory;
use Faker\Generator as Faker;

use Illuminate\Routing\Controller;

class TestEmailController extends Controller
{
    protected $emailBasePath = 'emails';

    protected $faker = NULL;

    public function show($name) {
        $viewData = [];
        switch ($name) {
                             // Borrower
            case 'borrower.approved':
            case 'borrower.approved_text':
                $viewData = $this->getBorrowerApprovedTestData();
                break;

            case 'borrower.completed':
            case 'borrower.completed_text':
                $viewData = $this->getBorrowerCompletedTestData();
                break;

            case 'borrower.reviewable':
            case 'borrower.reviewable_text':
                $viewData = $this->getBorrowerReviewableTestData();
                break;

                             // Invoice
            case 'invoice.paid':
            case 'invoice.paid_text':
                $viewData = $this->getInvoicePaidTestData();
                break;

                             // Loan
            case 'loan.canceled':
            case 'loan.canceled_text':
                $viewData = $this->getLoanCanceledTestData();
                break;

            case 'loan.created':
            case 'loan.created_text':
                $viewData = $this->getCommonLoanTestData();
                break;

            default;
                break;
        }

        return view($this->emailBasePath.'.'.$name, $viewData);
    }

    protected function getFaker() {
        if ($this->faker === NULL) {
            $this->faker = Factory::create('fr_CA');
        }

        return $this->faker;
    }

    protected function getCommonTestData() {
        $faker = $this->getFaker();

        $testData = [
            'title' => $faker->sentence,
        ];

        return $testData;
    }

    protected function getCommonLoanTestData() {
        $community = factory(Community::class)->make();

        $borrower_user = factory(User::class)->make();
        $borrower_user->communities()->attach($community->id, [ 'approved_at' => new \DateTime ]);
        $borrower = factory(Borrower::class)->make();
        $borrower->user = $borrower_user;

        $owner_user = factory(User::class)->make();
        $owner_user->communities()->attach($community->id, [ 'approved_at' => new \DateTime ]);
        $owner = factory(Owner::class)->make();
        $owner->user = $owner_user;

        $loanable = factory(Bike::class)->make();
        $loanable->owner = $owner;

        $loan = factory(Loan::class)->make();
        $loan->owner = $owner;
        $loan->borrower = $borrower;
        $loan->loanable = $loanable;

        $testData = array_merge(
            $this->getCommonTestData(),
            [
                'borrower_user' => $borrower,
                'borrower' => $borrower,
                'owner_user' => $owner,
                'owner' => $owner,
                'loanable' => $loanable,
                'loan' => $loan,
            ]
        );

        return $testData;
    }

    protected function getBorrowerApprovedTestData() {
        $testData = array_merge(
            $this->getCommonTestData(),
            [
                'user' => factory(User::class)->make(),
            ]
        );

        return $testData;
    }

    protected function getBorrowerCompletedTestData() {
        $testData = array_merge(
            $this->getCommonTestData(),
            [
                'user' => factory(User::class)->make(),
            ]
        );

        return $testData;
    }

    protected function getBorrowerReviewableTestData() {
        $testData = array_merge(
            $this->getCommonTestData(),
            [
                'user' => factory(User::class)->make(),
                'communities' => [
                    factory(Community::class)->make(),
                    factory(Community::class)->make(),
                ],
            ]
        );

        return $testData;
    }

    protected function getInvoicePaidTestData() {
        $faker = $this->getFaker();

        $testData = array_merge(
            $this->getCommonTestData(),
            [
                'text' => $faker->sentence(),
                'user' => factory(User::class)->make(),
                'invoice' => [
                    'id' => 1234,
                    'period' => '',

                    'total'  => 123.45,
                    'total_tps'  => 6.785,
                    'total_tvq'  => 12.35,
                    'total_with_taxes'  => 234.56,

                    'bill_items' => [
                        [ 'item_date' => 'tantot', 'label' => 'label', 'amount' => 1234 ],
                        [ 'item_date' => 'tantot', 'label' => 'label', 'amount' => 1234 ],
                        [ 'item_date' => 'tantot', 'label' => 'label', 'amount' => 1234 ],
                        [ 'item_date' => 'tantot', 'label' => 'label', 'amount' => 1234 ],
                    ],
                ],
            ]
        );

        return $testData;
    }

    protected function getLoanCanceledTestData() {
        $testData = $this->getCommonLoanTestData();

        $testData['receiver'] = $testData['borrower_user'];
        $testData['sender'] = $testData['owner_user'];

        return $testData;
    }

    protected function getLoanCreatedTestData() {
        $testData = $this->getCommonLoanTestData();

        return $testData;
    }
}
