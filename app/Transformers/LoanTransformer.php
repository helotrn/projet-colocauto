<?php

namespace App\Transformers;

use Molotov\Transformers\BaseTransformer;

class LoanTransformer extends BaseTransformer
{
    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        if (isset($output['actions']) && isset($output['extensions'])) {
            foreach ($output['extensions'] as $extension) {
                for ($i = 0; $i < count($output['actions']); $i++) {
                    $action = $output['actions'][$i];
                    if ($action['type'] === 'extension' && $action['id'] === $extension['id']) {
                        break;
                    }
                }

                $output['actions'][$i] = array_merge($output['actions'][$i], $extension);
            }
        }

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
