<?php

function wrap_array_keys($value) {
    if (!is_array($value)) {
        return [$value];
    }
    return array_keys($value);
}
