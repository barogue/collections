<?php

namespace Barogue\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The underlying data
     *
     * @var array
     */
    protected array $data = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Chainable constructor
     *
     * @param array $data
     *
     * @return static
     */
    public static function instance(array $data = []): static
    {
        return new static($data);
    }

    /**
     * Create a collection with a given range
     *
     * @param $start
     * @param $end
     * @param float|int $step
     *
     * @return static
     *
     * @see range()
     */
    public static function range($start, $end, float|int $step = 1): static
    {
        return new static(range($start, $end, $step));
    }

    /**
     * Changes the case of all keys in the collection
     *
     * @param int $case
     *
     * @return static
     *
     * @see array_change_key_case()
     */
    public function changeKeyCase(int $case = CASE_LOWER): static
    {
        $this->data = array_change_key_case($this->data, $case);
        return $this;
    }

    /**
     * Changes the case of all keys in the collection to lower case
     *
     * @return static
     *
     * @see array_change_key_case()
     * @see Collection::changeKeyCase()
     */
    public function changeKeyLowerCase(): static
    {
        return $this->changeKeyCase();
    }

    /**
     * Changes the case of all keys in the collection to upper case
     *
     * @return static
     *
     * @see array_change_key_case()
     * @see Collection::changeKeyCase()
     */
    public function changeKeyUpperCase(): static
    {
        return $this->changeKeyCase(CASE_UPPER);
    }

    /**
     * Split the collection into chunks
     *
     * @param int $length
     * @param bool $preserve_keys
     *
     * @return static
     *
     * @see array_chunk()
     */
    public function chunk(int $length, bool $preserve_keys = true): static
    {
        return static::instance(array_chunk($this->data, $length, $preserve_keys))->map(function ($chunk) {
            return static::instance($chunk);
        });
    }

    /**
     * Return the values from a single column in the input array
     *
     * @param int|string $key
     * @param int|string|null $index
     *
     * @return static
     *
     * @see array_column()
     */
    public function column(int|string $key, int|string $index = null): static
    {
        return new static(array_column($this->data, $key, $index));
    }

    /**
     * {@inheritDoc}
     *
     * @see \Countable::count()
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Count the number of times each value appears in the array
     *
     * @return static
     *
     * @see array_count_values()
     */
    public function countValues(): static
    {
        return new static(array_count_values($this->data));
    }

    /**
     * Flatten the nested collection into a new collection.
     *
     * By default, it will keep the keys but use a dot notation to indicate depth.
     *
     * @param bool $keys
     *
     * @return static
     */
    public function deflate(bool $keys = true): static
    {
        return new static(array_deflate($this->data, $keys));
    }

    /**
     * Compute the difference between other collections or arrays
     *
     * static|array ...$others
     *
     * @param mixed ...$others
     *
     * @return static
     *
     * @see array_diff()
     */
    public function diff(...$others): static
    {
        foreach ($others as &$other) {
            if ($other instanceof Collection) {
                $other = $other->getArray();
            }
        }
        unset($other);
        $other = array_shift($others);
        return new static(array_diff($this->data, $other, ...$others));
    }

    /**
     * Checks if the given key or index exists in the collection
     *
     * Allows for dot notation to query nested collections
     *
     * @param int|string $key
     *
     * @return bool
     *
     * @see array_key_exists()
     * @see array_exists()
     */
    public function exists(int|string $key): bool
    {
        return array_exists($this->data, $key);
    }

    /**
     * Filters elements of the collection using a callback function
     *
     * @param callable|null $callback
     * @param int $mode
     *
     * @return static
     *
     * @see array_filter()
     */
    public function filter(callable $callback = null, int $mode = 0): static
    {
        if ($callback === null) {
            return new static(array_filter($this->data));
        }
        return new static(array_filter($this->data, $callback, $mode));
    }

    /**
     * Get the first item in the collection.
     *
     * If a callback is provided then get the first item in the collection that passes the callback.
     *
     * If no item is ever found, either because there are no items or the callback never returns true, then return default.
     *
     * @param callable|null $callback
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function first(callable $callback = null, mixed $default = null): mixed
    {
        return array_first($this->data, $callback, $default);
    }

    /**
     * Gets the first key of the collection.
     *
     * If a callback is provided, then returns the first key that causes the callback to return true
     *
     * @param callable|null $callback
     * @param mixed|null $default
     *
     * @return int|string|null
     *
     * @see array_key_last()
     * @see https://www.php.net/manual/en/function.array_key_last.php
     */
    public function firstKey(callable $callback = null, mixed $default = null): int|string|null
    {
        return array_first_key($this->data, $callback, $default);
    }

    /**
     * Exchanges all keys with their associated values in the collection
     *
     * @return static
     */
    public function flip(): static
    {
        return new static(array_flip($this->data));
    }

    /**
     * Get the underlying array
     *
     * @return array
     */
    public function getArray(): array
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     *
     * @see \IteratorAggregate::getIterator()
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Join all the elements into a string with the glue string
     *
     * If a lastGlue is used then the last item will use a different glue string
     *
     * @param string $glue
     * @param string|null $lastGlue
     *
     * @return string
     */
    public function implode(string $glue = '', ?string $lastGlue = null): string
    {
        return array_join($this->data, $glue, $lastGlue);
    }

    /**
     * Expands a flattened collection into a new nested collection.
     *
     * This will not work on flattened arrays where the keys were not kept.
     *
     * @return static
     */
    public function inflate(): static
    {
        return new static(array_inflate($this->data));
    }

    /**
     * Determine if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Checks whether the collection is a list
     *
     * A collection is considered a list if its keys consist of consecutive numbers from 0 to count()
     *
     * @return bool
     *
     * @see array_is_list()
     */
    public function isList(): bool
    {
        return array_is_list($this->data);
    }

    /**
     * Return anew collection with the keys from this collection
     *
     * @param bool $recursive
     *
     * @return static
     */
    public function keys(bool $recursive = false): static
    {
        if ($recursive) {
            return $this->deflate()->keys();
        }
        return new static(array_keys($this->data));
    }

    /**
     * Get the last item in the collection.
     *
     * If a callback is provided then get the last item in the collection that passes the callback.
     *
     * If no item is ever found, either because there are no items or the callback never returns true, then return default.
     *
     * @param callable|null $callback
     * @param mixed|null $default
     *
     * @return mixed
     *
     * @see Collection::first()
     */
    public function last(callable $callback = null, mixed $default = null): mixed
    {
        return array_last($this->data, $callback, $default);
    }

    /**
     * Gets the last key of the collection
     *
     * If a callback is provided, then returns the last key that causes the callback to return true
     *
     * @param callable|null $callback
     * @param mixed $default
     *
     * @return int|string|null
     *
     * @see array_key_last()
     * @see https://www.php.net/manual/en/function.array_key_last.php
     */
    public function lastKey(callable $callback = null, mixed $default = null): int|string|null
    {
        return array_last_key($this->data, $callback, $default);
    }

    /**
     * Map the results on the collection into a new collection
     *
     * Setting $nested to true will deflate the collection first, then re-inflate, allowing you to map all nested items
     *
     * @param callable $callback
     * @param bool $nested
     *
     * @return static
     *
     * @see Collection::deflate()
     * @see Collection::inflate()
     */
    public function map(callable $callback, bool $nested = false): static
    {
        $data = $this->data;
        if ($nested) {
            $data = array_deflate($data);
        }
        $keys = array_keys($data);
        $values = array_map($callback, $data, $keys);
        $results = array_combine($keys, $values);
        if ($nested) {
            $results = array_inflate($results);
        }
        return new static($results);
    }

    /**
     * {@inheritDoc}
     *
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($offset): bool
    {
        return array_exists($this->data, $offset);
    }

    /**
     * {@inheritDoc}
     *
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($offset): mixed
    {
        return array_get($this->data, $offset);
    }

    /**
     * {@inheritDoc}
     *
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            array_set($this->data, $offset, $value);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset): void
    {
        array_unset($this->data, $offset);
    }

    /**
     * Reverse the items in to a new collection
     *
     * @return static
     */
    public function reverse(): static
    {
        return new static(array_reverse($this->data, true));
    }

    /**
     * Sort the collection in descending order
     *
     * @param int $flags
     *
     * @return static
     *
     * @see arsort()
     * @see https://www.php.net/manual/en/function.arsort.php
     */
    public function reverseSort(int $flags = SORT_REGULAR): static
    {
        $data = $this->data;
        arsort($data, $flags);
        return new static($data);
    }

    /**
     * Shuffle the collection
     *
     * @param bool $keepKeys
     *
     * @return static
     *
     * @see shuffle()
     * @see https://www.php.net/manual/en/function.shuffle.php
     */
    public function shuffle(bool $keepKeys = true): static
    {
        return new static(array_shuffle($this->data, $keepKeys));
    }

    /**
     * Sort the collection in ascending order
     *
     * @param int $flags
     *
     * @return static
     *
     * @see asort()
     * @see https://www.php.net/manual/en/function.asort.php
     */
    public function sort(int $flags = SORT_REGULAR): static
    {
        $data = $this->data;
        asort($data, $flags);
        return new static($data);
    }

    /**
     * Sort the collection using a callback
     *
     * @param callable $callback
     *
     * @return static
     *
     * @see uasort()
     * @see https://www.php.net/manual/en/function.uasort.php
     */
    public function sortCallback(callable $callback): static
    {
        $data = $this->data;
        uasort($data, $callback);
        return new static($data);
    }

    /**
     * Sort the collection by key in ascending order
     *
     * @param int $flags
     *
     * @return static
     *
     * @see ksort()
     * @see https://www.php.net/manual/en/function.ksort.php
     */
    public function sortKeys(int $flags = SORT_REGULAR): static
    {
        $data = $this->data;
        ksort($data, $flags);
        return new static($data);
    }

    /**
     * Sort the collection by keys using a user-defined comparison function
     *
     * @param callable $callback
     *
     * @return static
     *
     * @see uksort()
     * @see https://www.php.net/manual/en/function.uksort.php
     */
    public function sortKeysCallback(callable $callback): static
    {
        $data = $this->data;
        uksort($data, $callback);
        return new static($data);
    }

    /**
     * Returns the sum of values as an integer or float; 0 if the array is empty.
     *
     * @return int|float
     *
     * @see array_sum()
     */
    public function sum(): float|int
    {
        return array_sum($this->data);
    }

    /**
     * Reset the keys to array
     *
     * @return static
     *
     * @see array_values()
     * @see https://www.php.net/manual/en/function.array-values.php
     */
    public function values(): static
    {
        return new static(array_values($this->data));
    }
}
