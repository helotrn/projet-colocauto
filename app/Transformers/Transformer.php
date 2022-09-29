<?php

namespace App\Transformers;

use Illuminate\Support\Str;

class Transformer
{
    protected $contexts = [];

    public static $context = [];

    protected function authorize($item, $output, $options) {
        return $output;
    }

    public function transform($item, $options = []) {
        $fields = array_get($options, 'fields', []);
        if (!$fields || is_string($fields)) {
            $fields = ['*' => '*'];
        }

        if (in_array('*', array_keys($fields))) {
            $computedFields = array_diff(
                $item->computed,
                array_keys(array_get($options, '!fields', []))
            );
        } else {
            $computedFields = array_intersect(array_keys($fields), $item->computed);
        }
        $item->append($computedFields);
        $output = $item->toArray();

        $reflect = new \ReflectionClass($item);
        static::$context[$reflect->getShortName()] = $item->id;

        $this->addItems($output, $item, $options);
        $this->addCollections($output, $item, $options);
        foreach ($this->contexts as $context) {
            if (isset($options['context'][$context]) && $options['pivot']) {
                $this->includePivotsInContext($output, $options);
            }
        }

        if ($this->applyFieldsOption($options)) {
            $output = array_intersect_key(
                $output,
                array_merge(array_flip(array_keys($options['fields'])), ['id' => true])
            );
        }

        unset(static::$context[$reflect->getShortName()]);

        unset($output['pivot']);
        unset($output['laravel_through_key']);

        return $this->authorize($item, $output, $options);
    }

    protected function addCollections(&$output, &$item, &$options) {
        foreach (array_merge($item->collections, array_keys($item->morphManys)) as $relation) {
            $camelRelation = Str::camel($relation);
            if ($this->shouldIncludeRelation($relation, $item, $options)) {
                $className = get_class($item->{$camelRelation}()->getRelated());
                $transformer = new $className::$transformer();

                $parentClassName = get_class($item);

                if (method_exists($parentClassName, "{$camelRelation}Conditions")) {
                    $target = call_user_func(
                        "$parentClassName::{$relation}Conditions",
                        $item->{Str::camel($relation)},
                        static::$context
                    );
                } else {
                    $target = $item->{$camelRelation};
                }

                $output[$relation] = $target->map(
                    function ($p) use ($transformer, $options, $relation) {
                        $relationFields = [];
                        if (isset($options['fields'][$relation])
                            && $relation !== $options['fields'][$relation]) {
                            $relationFields = $options['fields'][$relation];
                        }

                        $notRelationFields = [];
                        if (isset($options['fields']["!$relation"])
                            && $relation !== $options['fields']["!$relation"]) {
                            $notRelationFields = $options['fields']["!$relation"];
                        }

                        return $transformer->transform($p, [
                            'fields' => $relationFields,
                            '!fields' => $notRelationFields,
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
                $camelRelation = Str::camel($relation);
                if (!$item->{$camelRelation}) {
                    $output[$relation] = null;
                    continue;
                }

                $className = get_class($item->{$camelRelation}()->getRelated());
                $transformer = new $className::$transformer();
                $output[$relation] = $transformer->transform($item->{$camelRelation}, [
                    'fields' => isset($options['fields'][$relation])
                        ? $options['fields'][$relation]
                        : [],
                    '!fields' => isset($options['fields']["!$relation"])
                        ? $options['fields']["!$relation"]
                        : [],
                    'pivot' => $item->pivot,
                    'context' => static::$context,
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
        return (!isset($options['fields']) ||
            in_array($field, wrap_array_keys($options['fields']), true) ||
            in_array('*', wrap_array_keys($options['fields']), true))
            && (!isset($options['!fields']) ||
                !in_array($field, wrap_array_keys($options['!fields']), true));
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

    protected function includePivotsInContext(&$output, $options) {
        foreach ($options['pivot']->toArray() as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            if ($this->shouldIncludeField($key, $options)) {
                $output[$key] = $options['pivot'][$key];
            }
        }

        foreach (array_merge(
            $options['pivot']->items,
            array_keys($options['pivot']->morphOnes)
        ) as $relation) {
            if ($this->shouldIncludeRelation($relation, $options['pivot'], $options)) {
                $output[$relation] = $options['pivot']->{$relation};
            }
        }

        foreach (array_merge(
            $options['pivot']->collections,
            array_keys($options['pivot']->morphManys)
        ) as $relation) {
            if ($this->shouldIncludeRelation($relation, $options['pivot'], $options)) {
                $output[$relation] = $options['pivot']->{$relation};
            }
        }
    }

    protected function filterKeys($output, $keys) {
        return array_intersect_key(
            $output,
            array_flip($keys)
        );
    }
}
