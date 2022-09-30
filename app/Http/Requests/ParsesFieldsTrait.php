<?php

namespace App\Http\Requests;

trait ParsesFieldsTrait
{
    private function joinFieldsTree(array $fields, $prefix = "", &$output = [])
    {
        foreach ($fields as $field => $rest) {
            if (is_array($rest)) {
                $this->joinFieldsTree(
                    $rest,
                    implode(".", array_filter([$prefix, $field])),
                    $output
                );
            } else {
                $output[] = implode(".", array_filter([$prefix, $field]));
            }
        }

        return $output;
    }

    private function splitFields($fields)
    {
        $parts = array_map("trim", explode(",", $fields));
        return array_map(function ($f) {
            return array_map("trim", explode(".", $f, 2));
        }, $parts);
    }

    private function parseFields($fields, &$acc = [])
    {
        return array_reduce(
            $fields,
            function (&$acc, $t) {
                switch (count($t)) {
                    case 2:
                        if (!isset($acc[$t[0]])) {
                            $acc[$t[0]] = [];
                        }

                        if (strpos($t[1], ".") !== false) {
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
            },
            $acc
        );
    }
}
