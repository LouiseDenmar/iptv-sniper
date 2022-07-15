<?php

declare(strict_types=1);

namespace Inspirum\Arrayable;

use JsonSerializable;
use Stringable;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements \Inspirum\Arrayable\Arrayable<TKey, TValue>
 */
abstract class BaseModel implements Arrayable, JsonSerializable, Stringable
{
    /**
     * @return array<TKey, TValue>
     */
    abstract public function __toArray(): array;

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->__toArray();
    }

    /**
     * @return array<TKey, TValue>
     */
    public function jsonSerialize(): array
    {
        return $this->__toArray();
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR);
    }
}
