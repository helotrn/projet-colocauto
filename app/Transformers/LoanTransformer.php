<?php

namespace App\Transformers;

use Auth;
use Molotov\Transformers\BaseTransformer;

class LoanTransformer extends BaseTransformer
{
    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        foreach (['intention', 'pre_payment', 'takeover', 'handover'] as $key) {
            if (isset($output[$key]) && isset($output['actions'])) {
                for ($i = 0; $i < count($output['actions']); $i++) {
                    if ($output['actions'][$i]['type'] === $key) {
                        break;
                    }
                }

                $action = array_merge(
                    $output[$key],
                    [
                        'type' => $key,
                    ]
                );
                $output['actions'][$i] = $action;
            }
        }

        return $output;
    }
}
