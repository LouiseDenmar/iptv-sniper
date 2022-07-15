<?php

use Inspirum\Arrayable\Convertor;

if (function_exists('is_arrayable') === false) {
    /**
     * Can be cast to array
     */
    function is_arrayable(mixed $data): bool
    {
        return Convertor::isArrayable($data);
    }
}

if (function_exists('to_array') === false) {
    /**
     * Cast anything to array
     *
     * @return array<int|string, mixed>
     */
    function to_array(mixed $data, ?int $limit = null): array
    {
        return Convertor::toArray($data, $limit);
    }
}