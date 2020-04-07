<?php

namespace App\Transformers;

use Auth;
use Molotov\Transformers\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    protected $contexts = ['Community'];

    public function authorize($item, $output, $user = null) {
        if ($user && ($user->isAdmin() || $user->id === $item->id)) {
            return $output;
        }

        $publicFields = [
            'id',
            'name',
            'last_name',
            'full_name',
            'avatar',
            'tags',
            'description',
            'owner',
        ];

        if (isset($options['context']['Loan'])) {
            $publicFields[] = 'phone';
        }

        return $this->filterKeys($output, $publicFields);
    }

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

        return $this->authorize($item, $output, Auth::user());
    }
}
