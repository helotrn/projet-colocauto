<?php

namespace App\Transformers;

use App\Models\Community;
use Auth;
use Molotov\Transformers\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if (isset($options['context']['Community']) && $options['pivot']
          && $this->shouldIncludeField('role', $options)) {
            $output['role'] = $options['pivot']['role'];
        }

        return $output;
    }
}
