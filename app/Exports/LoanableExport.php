<?php

namespace App\Exports;

use Molotov\Exports\BaseExport;

class LoanableExport extends BaseExport
{
    public function __construct($items, $fields, $model)
    {
        $this->items = $items;

        // Should match that of store/models/loanables.js.
        $this->fields = [
            "id",
            "name",
            "type",
            "comments",
            "instructions",
            "location_description",
            "position",
            "community_ids",
            "owner.id",
            "owner.user.id",
            "owner.user.name",
            "owner.user.last_name",
            "owner.user.communities.0.id",
            "owner.user.communities.0.name",
            "car_insurer",
        ];
    }
}
