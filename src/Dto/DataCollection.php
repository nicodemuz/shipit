<?php

declare(strict_types=1);

namespace Cline\Shipit\Dto;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
final class DataCollection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param array<TKey, TValue> $items
     * @param class-string|null   $itemClass
     */
    public function __construct(
        private array $items = [],
        private readonly ?string $itemClass = null,
    ) {}

    /**
     * @param array<int|string, mixed> $items
     * @param class-string             $itemClass
     */
    public static function create(array $items, string $itemClass): self
    {
        $mapped = [];
        foreach ($items as $key => $item) {
            if ($item instanceof $itemClass) {
                $mapped[$key] = $item;
                continue;
            }

            if (!is_array($item)) {
                throw new InvalidArgumentException(sprintf(
                    'Expected array or %s for collection item, got %s.',
                    $itemClass,
                    get_debug_type($item),
                ));
            }

            if (!is_subclass_of($itemClass, Data::class) && $itemClass !== Data::class) {
                throw new InvalidArgumentException(sprintf(
                    'Collection item class %s must extend %s.',
                    $itemClass,
                    Data::class,
                ));
            }

            /** @var class-string<Data> $itemClass */
            $mapped[$key] = $itemClass::from($item);
        }

        return new self($mapped, $itemClass);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->items as $key => $item) {
            $result[$key] = $item instanceof Data ? $item->toArray() : $item;
        }

        return $result;
    }

    /**
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->items[] = $value;

            return;
        }

        $this->items[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }
}
