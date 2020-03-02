<?php

namespace App\Transformers;

use App\Models\Community;
use Auth;
use Molotov\Transformers\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if (isset($options['context']['Community']) && $options['pivot']) {
            foreach ($options['pivot']->toArray() as $key => $value) {
                if ($key === 'id') {
                    continue;
                }

                if ($this->shouldIncludeField($key, $options)) {
                    $output[$key] = $options['pivot'][$key];
                }
            }

            foreach ($options['pivot']->morphOnes as $key => $target) {
                if ($this->shouldIncludeRelation($key, $options['pivot'], $options)) {
                    $output[$key] = $options['pivot']->{$key};
                }
            }
        }

        return $output;
    }
}
