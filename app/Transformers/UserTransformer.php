<?php

namespace App\Transformers;

use App\Models\Community;
use Auth;

class UserTransformer extends BaseTransformer
{
    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if (isset($options['context']['Community']) && $options['pivot']) {
            $output['role'] = $options['pivot']['role'];
        }

        return $output;
    }
}
