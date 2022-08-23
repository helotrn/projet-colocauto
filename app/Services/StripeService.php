<?php

namespace App\Services;

use App\Models\User;
use Log;
use Stripe\Exception\CardException;

class StripeService
{
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        \Stripe\Stripe::setApiKey($this->apiKey);
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
        $description
    ) {
        return \Stripe\Charge::create([
            "amount" => $amountWithFeeInCents,
            "currency" => "cad",
            "customer" => $customerId,
            "description" => $description,
        ]);
    }
}
