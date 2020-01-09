<?php

namespace App\Transformers;

use App\Models\Community;
use Auth;

class UserTransformer extends BaseTransformer
{
    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if (Auth::user()->role === 'admin') {
            if ($this->shouldIncludeField('communities', $options)) {
                $output['communities'] = $this->addCollection(
                    'communities',
                    Community::all(),
                    new Community::$transformer(),
                    $options
                );
            }
        }

        return $output;
    }
}
