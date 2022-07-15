<?php

declare(strict_types=1);

namespace Inspirum\Arrayable;

/**
 * @template TKey of array-key
 * @template TValue
 */
interface Arrayable
{
    /**
     * @return array<TKey, TValue>
     */
    public function __toArray(): array;
}
