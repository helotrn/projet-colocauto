<?php

namespace App\Transformers;

use Auth;
use Molotov\Transformers\BaseTransformer;

class CommunityTransformer extends BaseTransformer
{
    protected $contexts = ['User'];

    public function transform($item, $options = []) {
        $output = parent::transform($item, $options);

        $user = Auth::user();
        if ($user && $user->isAdmin()) {
            return $output;
        }

        if (!$user) {
            unset($output['users']);
        }

        $approvedCommunity = $user->communities
            ->where('id', $item->id)
            ->where('pivot.approved_at', '!=', null);
        if ($approvedCommunity->isEmpty()) {
            unset($output['users']);
        }

        return $output;
    }
}
