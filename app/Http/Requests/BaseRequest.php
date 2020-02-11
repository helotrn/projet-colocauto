<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    private $fieldsMemo = null;

    public function authorize() {
        return true;
    }

    public function rules() {
        return [];
    }

    public function message() {
        return [];
    }

    public function getFields() {
        if ($this->fieldsMemo !== null) {
            return $this->fieldsMemo;
        }

        if (!$this->get('fields')) {
            return $this->fieldsMemo = ['*' => '*'];
        }

        return $this->fieldsMemo = $this->parseFields(
            $this->splitFields($this->get('fields'))
        );
    }

    private function splitFields($fields) {
        $parts = array_map('trim', explode(',', $fields));
        return array_map(function ($f) {
            return array_map('trim', explode('.', $f, 2));
        }, $parts);
    }

    private function parseFields($fields, &$acc = []) {
        return array_reduce($fields, function (&$acc, $t) {
            switch (count($t)) {
                case 2:
                    if (!isset($acc[$t[0]])) {
                        $acc[$t[0]] = [];
                    }

                    if (strpos($t[1], '.') !== false) {
                        $st = $this->splitFields($t[1]);
                        $acc[$t[0]] = $this->parseFields($st, $acc[$t[0]]);
                    } else {
                        if (is_string($acc[$t[0]])) {
                            $str = $acc[$t[0]];
                            $acc[$t[0]] = [];
                            $acc[$t[0]][$str] = $str;
                        }
                        $acc[$t[0]][$t[1]] = $t[1];
                    }
                    break;
                case 1:
                    $acc[$t[0]] = $t[0];
                    break;
                default:
                    $acc[] = $t;
                    break;
            }
            return $acc;
        }, $acc);
    }
}
