<?php

namespace App\Transformers;

use Auth;

class CarTransformer extends LoanableTransformer
{
    public function authorize($item, $output, $options) {
        $user = Auth::user();

        if ($user && ($user->isAdmin() || $user->id === $output['id'])) {
            return $output;
        }

        $publicFields = [
            'id',
            'name',
            'position',
            'location_description',
            'comments',
            'instructions',
            'owner_id',
            'community_id',
            'brand',
            'model',
            'year_of_circulation',
            'transmission_mode',
            'engine',
        ];

        if (isset($options['context']['Loan'])) {
            $publicFields[] = 'papers_location';
        }

        return $this->filterKeys($output, $publicFields);
    }

    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        return $this->authorize($item, $output, $options);
    }
}
