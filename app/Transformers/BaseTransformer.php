<?php

namespace App\Transformers;

class BaseTransformer
{
    public static $context = [];

    public function transform($item, $options = []) {
        $fields = array_get($options, 'fields', []);
        if (is_string($fields)) {
            $fields = ['*' => '*'];
        }

        if (in_array('*', array_keys($fields))) {
            $computedFields = $item->computed;
        } else {
            $computedFields = array_intersect(array_keys($fields), $item->computed);
        }
        $item->append($computedFields);
        $output = $item->toArray();

        $reflect = new \ReflectionClass($item);
        static::$context[$reflect->getShortName()] = $item->id;

        $this->addItems($output, $item, $options);
        $this->addCollections($output, $item, $options);

        if ($this->applyFieldsOption($options)) {
            $output = array_intersect_key(
                $output,
                array_flip(array_keys($options['fields']))
            );
        }

        unset(static::$context[$reflect->getShortName()]);

        unset($output['pivot']);

        return $output;
    }

    protected function addCollections(&$output, &$item, &$options) {
        foreach ($item->collections as $relation) {
            if ($this->shouldIncludeRelation($relation, $item, $options)) {
                $className = get_class($item->{$relation}()->getRelated());
                $transformer = new $className::$transformer();

                $parentClassName = get_class($item);

                if (method_exists($parentClassName, "{$relation}Conditions")) {
                    $target = call_user_func(
                        "$parentClassName::{$relation}Conditions",
                        $item->{$relation},
                        static::$context
                    );
                } else {
                    $target = $item->{$relation};
                }

                $output[$relation] = $target->map(
                    function ($p) use ($transformer, $options, $relation) {
                        $relationFields = [];
                        if (isset($options['fields'][$relation])
                            && $relation !== $options['fields'][$relation]) {
                            $relationFields = $options['fields'][$relation];
                        }

                        return $transformer->transform($p, [
                            'fields' => $relationFields,
                            'pivot' => $p->pivot,
                            'context' => static::$context,
                        ]);
                    }
                );
            }
        }
    }

    protected function addItems(&$output, &$item, &$options) {
        foreach (array_merge($item->items, array_keys($item->morphOnes)) as $relation) {
            if ($this->shouldIncludeRelation($relation, $item, $options)) {
                if (!$item->{$relation}) {
                    $output[$relation] = null;
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

    protected function shouldIncludeRelation($relation, &$item, $options) {
        return isset($options['fields']) &&
            (in_array($relation, wrap_array_keys($options['fields']), true) ||
            isset($options['fields']['*']['*']) ||
            in_array($relation, $item->getWith(), true));
    }

    protected function shouldIncludeField($field, $options) {
        return !isset($options['fields']) ||
            in_array($field, wrap_array_keys($options['fields']), true) ||
            in_array('*', wrap_array_keys($options['fields']), true);
    }

    protected function addCollection($relation, $target, $transformer, $options) {
        return $target->map(function ($p) use ($relation, $transformer, $options) {
            $relationFields = [];
            if (isset($options['fields'][$relation])
                && $relation !== $options['fields'][$relation]) {
                $relationFields = $options['fields'][$relation];
            }

            return $transformer->transform($p, [
                'fields' => $relationFields,
            ]);
        });
    }

    protected function applyFieldsOption($options) {
        return array_key_exists('fields', $options) &&
            is_array($options['fields']) &&
            !empty($options['fields']) &&
            !in_array('*', array_keys($options['fields']));
    }
}
