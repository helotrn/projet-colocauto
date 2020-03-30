<?php

namespace App\Transformers;

use Molotov\Transformers\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    protected $contexts = ['Community'];

    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if (isset($item->pivot->tags)) {
            if ($this->shouldIncludeRelation('tags', $item, $options)) {
                $transformer = new BaseTransformer;

                $output['tags'] = array_merge(
                    $output['tags']->toArray(),
                    $item->pivot->tags->map(function ($t) use ($transformer) {
                        return $transformer->transform($t);
                    })->toArray()
                );
            }
        }

        if ($this->shouldIncludeRelation('borrower', $item, $options)) {
            $output['borrower'] = $item->borrower ?: new \stdClass;
        }

        if (isset($output['balance'])) {
            // Approximation but more convenient for display
            $output['balance'] = floatval($output['balance']);
        }

        return $output;
    }
}
