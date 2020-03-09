<?php

namespace App\Transformers;

use Molotov\Transformers\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    protected $contexts = ['Community'];

    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if ($this->shouldIncludeRelation('borrower', $item, $options)) {
            $output['borrower'] = $item->borrower ?: new \stdClass;
        }

        return $output;
    }
}
