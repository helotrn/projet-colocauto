<?php

namespace App\Transformers;

use Molotov\Transformers\BaseTransformer;

class CommunityTransformer extends BaseTransformer
{
    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if ($this->shouldIncludeRelation('proof', $item, $options)) {
            $output['proof'] = $item->pivot->proof;
        }

        if (isset($options['context']['User']) && $options['pivot']
          && $this->shouldIncludeField('role', $options)) {
            $output['role'] = $options['pivot']['role'];
        }

        return $output;
    }
}
