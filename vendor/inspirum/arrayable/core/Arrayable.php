<?php

declare(strict_types=1);

if (interface_exists('\Arrayable') === false) {
    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @extends \Inspirum\Arrayable\Arrayable<TKey, TValue>
     */
    interface Arrayable extends \Inspirum\Arrayable\Arrayable
    {
    }
}

