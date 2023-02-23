<?php

namespace App\Observers;

use Auth;
use App\Models\RefundChange;
use App\Models\Refund;
use App\Models\User;

class RefundsObserver
{
    public function saved($refund)
    {
        if ($refund->wasRecentlyCreated == false) {
            if($refund->changeDescription) {
              // save changes in the database when saved event is triggered once again
              RefundChange::create([
                  'user_id'     => Auth::user()->id,
                  'refund_id'  => $refund->id,
                  'description' => $refund->changeDescription,
              ]);
            } else {
              // Describe each change
              $changes = $refund->getChanges();
              if( array_key_exists("amount", $changes)
                && $changes["amount"] != $refund->getOriginal("amount")
              ) {
                $refund->changeDescription = $refund->getOriginal("amount") . " => " . $refund->getChanges()["amount"];
              }
              if( array_key_exists("credited_user_id", $changes)
                && $changes["credited_user_id"] != $refund->getOriginal("credited_user_id")
              ) {
                $users = User::all();
                if($refund->changeDescription) $refund->changeDescription .= ", ";
                $refund->changeDescription .= 'Payé à : ' . $users->find($refund->getOriginal("credited_user_id"))->full_name . " => " . $users->find($refund->getChanges()["credited_user_id"])->full_name;
              }
              if( array_key_exists("user_id", $changes)
                && $changes["user_id"] != $refund->getOriginal("user_id")
              ) {
                $users = User::all();
                if($refund->changeDescription) $refund->changeDescription .= ", ";
                $refund->changeDescription .= 'Payé par : ' . $users->find($refund->getOriginal("user_id"))->full_name . " => " . $users->find($refund->getChanges()["user_id"])->full_name;
              }
            }
        }
    }
}
