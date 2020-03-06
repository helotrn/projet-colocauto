<?php

namespace App\Transformers;

use Molotov\Transformers\BaseTransformer;

class CommunityTransformer extends BaseTransformer
{
    protected $contexts = ['User'];

    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);



        }

        return $output;
    }
}
