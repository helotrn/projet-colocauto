<?php

namespace App\Transformers;

use Auth;
use Molotov\Transformers\BaseTransformer;

class LoanTransformer extends BaseTransformer
{
    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if (isset($output['intention']) && isset($output['actions'])) {
            for ($i = 0; $i < count($output['actions']); $i++) {
                if ($output['actions'][$i]['type'] === 'intention') {
                    break;
                }
            }

            $intentionAction = array_merge(
                $output['intention'],
                [
                    'type' => 'intention',
                ]
            );
            $output['actions'][$i] = $intentionAction;
        }

        return $output;
    }
}
