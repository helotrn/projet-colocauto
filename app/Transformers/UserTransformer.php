<?php

namespace App\Transformers;

use Auth;
use Molotov\Transformer;

class UserTransformer extends Transformer
{
    protected $contexts = ['Community'];

    public function authorize($item, $output, $options) {
        $user = Auth::user();

        // If the user is...
        if ($user) {
            // ...a global admin or itself
            if ($user->isAdmin() || $user->id === $item->id) {
                // ...display everything
                return $output;
            }
            // ...a community admin
            if ($user->isAdminOfCommunityFor($item->id)) {
                $adminOfCommunityFields = [
                    'id',
                    'avatar',
                    'communities',
                    'date_of_birth',
                    'address',
                    'postal_code',
                    'phone',
                    'is_smart_phone',
                    'other_phone',
                    'description',
                    'email',
                    'full_name',
                    'last_name',
                    'loanables',
                    'loans',
                    'name',
                    'owner',
                    'tags',
                ];

                return $this->filterKeys($output, $adminOfCommunityFields);
            }
        }

        // ...otherwise, limit the visible fields...
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

        // ...except when we are in the context of a loan
        if (isset($options['context']['Loan'])) {
            $publicFields[] = 'phone';
        }

        return $this->filterKeys($output, $publicFields);
    }

    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if (isset($item->pivot->tags)) {
            if ($this->shouldIncludeRelation('tags', $item, $options)) {
                $transformer = new Transformer;

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

        return $this->authorize($item, $output, $options);
    }
}
