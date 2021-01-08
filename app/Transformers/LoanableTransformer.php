<?php

namespace App\Transformers;

use Auth;
use Molotov\Transformer;

class LoanableTransformer extends Transformer
{
    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        $user = Auth::user();
        if ($user && $user->isAdmin()) {
            return $output;
        }

        unset($output['padlock']);

        return $output;
    }
}
