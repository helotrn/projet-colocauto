<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BaseExport implements FromCollection, WithHeadings, WithMapping
{
    protected $items;
    protected $fields;

    public function __construct($items, $fields) {
        $this->items = $items;
        $this->fields = $fields;
    }

    public function headings(): array {
        return $this->fields;
    }

    public function map($item): array {
        return  array_map(function ($f) use ($item) {
            return array_get($item, $f);
        }, $this->fields);
    }

    public function collection() {
        return $this->items;
    }
}
