<?php

namespace App\Transformers;

class BaseTransformer
{
    public static $context = [];

    public function transform($item, $options = []) {
        $output = $item->toArray();

        $reflect = new \ReflectionClass($item);
        static::$context[$reflect->getShortName()] = $item->id;

        $this->addBelongsTo($output, $item, $options);
        $this->addCollections($output, $item, $options);

        if ($this->applyFieldsOption($options)) {
            $output = array_intersect_key(
                $output,
                array_flip(array_keys($options['fields']))
            );
        }

        static::$context = [];

        return $output;
    }

    protected function addCollections(&$output, &$item, &$options) {
        foreach ($item->collections as $relation) {
            if (!isset($options['fields']) ||
                in_array($relation, wrap_array_keys($options['fields']), true)) {
                $className = get_class($item->{$relation}()->getRelated());
                $transformer = new $className::$transformer();

                $parentClassName = get_class($item);
                if (method_exists($parentClassName, "{$relation}Conditions")) {
                    $target = call_user_func("$parentClassName::{$relation}Conditions", $item->{$relation}, static::$context);
                } else {
                    $target = $item->{$relation};
                }

                $output[$relation] = $target->map(function ($p) use ($transformer, $options, $relation) {
                    return $transformer->transform($p, [
                        'fields' => isset($options['fields'][$relation])
                        ? $options['fields'][$relation]
                        : null,
                    ]);
                });
            }
        }
    }

    protected function addBelongsTo(&$output, &$item, &$options) {
        foreach ($item->belongsTo as $relation) {
            if (!isset($options['fields']) || in_array($relation, wrap_array_keys($options['fields']), true)) {
                if (!$item->{$relation}) {
                    continue;
                }

                $className = get_class($item->{$relation}()->getRelated());
                $transformer = new $className::$transformer();
                $output[$relation] = $transformer->transform($item->{$relation}, [
                    'fields' => isset($options['fields'][$relation])
                        ? $options['fields'][$relation]
                        : null
                ]);
            }
        }
    }

    private function applyFieldsOption($options) {
        return array_key_exists('fields', $options) &&
            is_array($options['fields']) &&
            !empty($options['fields']) &&
            !in_array('*', $options['fields']);
    }
}
