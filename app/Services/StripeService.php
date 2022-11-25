<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;

class StripeService
{
    private $apiKey;

    // Keep in sync with resources/app/src/helpers/transactionFees.js
    public static $feeSpec = [
        "amex" => [
            "ratio" => 0.035,
            "constant" => 0,
        ],
        "foreign" => [
            "ratio" => 0.032,
            "constant" => 0.3,
        ],
        "default" => [
            "ratio" => 0.022,
            "constant" => 0.3,
        ],
    ];

    // Passing fees on to customer:
    // https://support.stripe.com/questions/passing-the-stripe-fee-on-to-customers
    public static function computeAmountWithFee($amount, $paymentMethod)
    {
        $feeType = "default";

        if ($paymentMethod->credit_card_type === "American Express") {
            $feeType = "amex";
        } elseif ($paymentMethod->country !== "CA") {
            $feeType = "foreign";
        }

        $feeConstant = static::$feeSpec[$feeType]["constant"];
        $feeRatio = static::$feeSpec[$feeType]["ratio"];

        return round(($amount + $feeConstant) / (1 - $feeRatio), 2);
    }

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        \Stripe\Stripe::setApiKey($this->apiKey);
    }

    public function getSource(PaymentMethod $method)
    {
        $customer = $this->getUserCustomer($method->user);
        try {
            return \Stripe\Customer::retrieveSource(
                $customer->id,
                $method->external_id
            );
        } catch (ApiErrorException $e) {
            Log::error($e);
            return null;
        }
    }

    public function getUserCustomer(User $user)
    {
        $customers = \Stripe\Customer::all([
            "email" => $user->email,
            "limit" => 1,
        ]);

        $customer = array_pop($customers->data);

        if ($customer) {
            return $customer;
        }

        return \Stripe\Customer::create([
            "description" => "{$user->full_name} <{$user->email}> ({$user->id})",
            "email" => $user->email,
            "name" => $user->full_name,
            "address" => [
                "line1" => $user->address,
                "country" => "CA",
                "postal_code" => $user->postal_code,
            ],
        ]);
    }

    public function createCardBySourceId($customerId, $sourceId)
    {
        try {
            return \Stripe\Customer::createSource($customerId, [
                "source" => $sourceId,
            ]);
        } catch (CardException $e) {
            throw new ValidationException([
                "stripe" => $e->getMessage(),
            ]);
        }
    }

    public function deleteSource($customerId, $sourceId)
    {
        try {
            \Stripe\Customer::deleteSource($customerId, $sourceId);
        } catch (\Exception $e) {
            // Doesn't really matter
        }
    }

    public function createCharge(
        $amountWithFeeInCents,
        $customerId,
        $description,
        $paymentMethodId
    ) {
        return \Stripe\Charge::create([
            "amount" => $amountWithFeeInCents,
            "currency" => "eur",
            "source" => $paymentMethodId,
            "customer" => $customerId,
            "description" => $description,
        ]);
    }
}
