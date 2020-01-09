<?php

function wrap_array_keys($value) {
    if (!is_array($value)) {
        return [$value];
    }
    return array_keys($value);
}

function dig($target, $key, $default = null) {
    $parts = explode('.', $key, 2);

    if (count($parts) === 1) {
        if (isset($target[$key]) && $target[$key] !== null) {
            return $target[$key];
        }
    }

    $key = $parts[0];
    $rest = isset($parts[1]) ? $parts[1] : '';

    if (isset($target[$key]) && $target[$key] !== null) {
        return dig($target[$key], $rest, $default);
    }

    return $default;
}
