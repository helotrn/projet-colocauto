<?php

namespace App\Transformers;

use App\Models\Loan;
use Auth;
use Molotov\Transformer;

class LoanableTransformer extends Transformer
{
    public function transform($item, $options = [])
    {
        $user = Auth::user();
        if ($user) {
            // Loan context means a loan was transformed first.
            if (array_key_exists("Loan", $options["context"])) {
                // TODO: avoid re-fetching by saving the loan in the context, rather
                // than only its id.
                $loan = Loan::find($options["context"]["Loan"]);
                $item->handleInstructionVisibilityFor($user, $loan);
            } else {
                $item->handleInstructionVisibilityFor($user);
            }
        }
        $output = parent::transform($item, $options);

        if ($user && $user->isAdmin()) {
            return $output;
        }

        unset($output["padlock"]);

        return $output;
    }
}
