<?php

namespace App\Transformers;

use Auth;
use Molotov\Transformer;

class LoanableTransformer extends Transformer
{
    public function transform($item, $options = [])
    {
        $user = Auth::user();
        if ($user) {
            $item->handleInstructionVisibilityFor($user);
        }
        $output = parent::transform($item, $options);

        if ($user && $user->isAdmin()) {
            return $output;
        }

        unset($output["padlock"]);

        return $output;
    }
}
