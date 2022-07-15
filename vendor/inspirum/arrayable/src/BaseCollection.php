<?php

declare(strict_types=1);

namespace Inspirum\Arrayable;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Stringable;
use Traversable;
use function array_key_exists;
use function array_map;
use function count;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @template TItemKey of array-key
 * @template TItemValue
 * @template TKey of array-key
 * @template TValue of \Inspirum\Arrayable\Arrayable<TItemKey, TItemValue>
 * @implements \ArrayAccess<TKey, TValue>
 * @implements \IteratorAggregate<TKey, TValue>
 * @implements \Inspirum\Arrayable\Arrayable<TKey, array<TItemKey, TItemValue>>
 */
abstract class BaseCollection implements ArrayAccess, Countable, IteratorAggregate, Arrayable, JsonSerializable, Stringable
{
    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(
        protected array $items,
    ) {
    }

    /**
     * @param TKey $key
     */
    public function offsetExists(mixed $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param TKey $key
     *
     * @return TValue
     */
    public function offsetGet(mixed $key): mixed
    {
        return $this->items[$key];
    }

    /**
     * @param TKey   $key
     * @param TValue $value
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * @param TValue $value
     */
    public function offsetAdd(mixed $value): void
    {
        $this->items[] = $value;
    }

    /**
     * @param TKey $key
     */
    public function offsetUnset(mixed $key): void
    {
        unset($this->items[$key]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return \Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array<TKey, array<TItemKey, TItemValue>>
     */
    public function __toArray(): array
    {
        return array_map(static fn(Arrayable $item): array => $item->__toArray(), $this->items);
    }

    /**
     * @return array<TKey, array<TItemKey, TItemValue>>
     */
    public function toArray(): array
    {
        return $this->__toArray();
    }

    /**
     * @return array<TKey, array<TItemKey, TItemValue>>
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
