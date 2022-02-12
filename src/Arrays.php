<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-util
 */
declare(strict_types=1);

namespace froq\util;

use KeyError, ValueError;

/**
 * Arrays.
 *
 * @package froq\util
 * @object  froq\util\Arrays
 * @author  Kerem Güneş
 * @since   1.0
 * @static
 */
final class Arrays extends \StaticClass
{
    /**
     * Check whether all keys are "int" in given array, or given array is a list when strict.
     *
     * @param  array $array
     * @param  bool  $strict
     * @return bool
     */
    public static function isList(array $array, bool $strict = true): bool
    {
        if ($strict) {
            return array_is_list($array);
        }

        foreach (array_keys($array) as $key) {
            if (!is_int($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check whether given array is an associative array.
     *
     * @param  array $array
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        foreach (array_keys($array) as $key) {
            if (is_string($key) || $key < 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether all keys are "string" in given array.
     *
     * @param  array $array
     * @return bool
     */
    public static function isMap(array $array): bool
    {
        foreach (array_keys($array) as $key) {
            if (!is_string($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check whether all values are "unique" in given array.
     *
     * @param  array $array
     * @param  bool  $strict
     * @return bool
     */
    public static function isSet(array $array, bool $strict = true): bool
    {
        $search = array_shift($array);
        foreach ($array as $value) {
            if ($strict ? ($search === $value) : ($search == $value)) {
                return false;
            }
            $search = $value;
        }
        return true;
    }

    /**
     * Put an item into given array, with dot notation support for sub-array paths.
     *
     * @param  array      &$array
     * @param  int|string  $key
     * @param  any         $value
     * @return array
     */
    public static function set(array &$array, int|string $key, $value): array
    {
        // Usage:
        // Arrays::set($array, 'a.b.c', 1) => ['a' => ['b' => ['c' => 1]]]

        if (array_key_exists($key, $array)) { // Direct access.
            $array[$key] = $value;
        } else {
            $key = (string) $key;

            if (!str_contains($key, '.')) {
                $array[$key] = $value;
            } else {
                $keys = explode('.', trim($key, '.'));

                if (count($keys) <= 1) { // Direct access.
                    $array[$keys[0]] = $value;
                } else { // Path access (with dot notation).
                    $current =& $array;

                    foreach ($keys as $key) {
                        if (isset($current[$key])) {
                            $current[$key] = (array) $current[$key];
                        }
                        $current =& $current[$key];
                    }

                    $current = $value;
                    unset($current);
                }
            }
        }

        return $array;
    }

    /**
     * Bridge method to set() for multiple items.
     *
     * @param  array &$array
     * @param  array  $items
     * @return array
     * @since  4.0
     */
    public static function setAll(array &$array, array $items): array
    {
        foreach ($items as $key => $value) {
            self::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Get an item form given array, with dot notation support for sub-array paths.
     *
     * @param  array      &$array
     * @param  int|string  $key AKA path.
     * @param  any|null    $default
     * @param  bool        $drop
     * @return any|null
     */
    public static function get(array &$array, int|string $key, $default = null, bool $drop = false)
    {
        // Usage:
        // $array = ['a' => ['b' => ['c' => ['d' => 1, 'd.e' => '...']]]]
        // Arrays::get($array, 'a.b.c.d') => 1
        // Arrays::get($array, 'a.b.c.d.e') => '...'

        $value = $default;
        if (empty($array)) {
            return $value;
        }

        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            if ($drop) { // Drop gotten item.
                unset($array[$key]);
            }
        } else {
            $key = (string) $key;

            if (!str_contains($key, '.')) {
                $value = $array[$key] ?? $value;
                if ($drop) { // Drop gotten item.
                    unset($array[$key]);
                }
            } else {
                $keys = explode('.', trim($key, '.'));
                $key  = array_shift($keys);

                // No more dig.
                if (empty($keys)) {
                    if (array_key_exists($key, $array)) {
                        $value = $array[$key];
                        if ($drop) { // Drop gotten item.
                            unset($array[$key]);
                        }
                    }
                }
                // Dig more..
                elseif (isset($array[$key]) && is_array($array[$key])) {
                    $value = self::get($array[$key], implode('.', $keys), $value, $drop);
                }
            }
        }

        return $value;
    }

    /**
     * Bridge method to get() for multiple items. Useful in some times eg. list(..) = Arrays::getAll(..).
     *
     * @param  array             &$array
     * @param  array<int|string>  $keys AKA paths.
     * @param  any|null           $default
     * @param  bool               $drop
     * @return array
     */
    public static function getAll(array &$array, array $keys, $default = null, bool $drop = false): array
    {
        $values = [];

        foreach ($keys as $key) {
            $values[] = self::get($array, $key, $default, $drop);
        }

        return $values;
    }

    /**
     * Pull an item from given array by a key.
     *
     * @param  array      &$array
     * @param  int|string  $key
     * @param  any|null    $default
     * @return any|null
     */
    public static function pull(array &$array, int|string $key, $default = null)
    {
        return self::get($array, $key, $default, true);
    }

    /**
     * Bridge method to get() for multiple items. Useful in some times eg. list(..) = Arrays::pullAll(..).
     *
     * @param  array             &$array
     * @param  array<int|string>  $keys
     * @param  any|null           $default
     * @return array
     */
    public static function pullAll(array &$array, array $keys, $default = null): array
    {
        return self::getAll($array, $keys, $default, true);
    }

    /**
     * Add (append) an item to data array, flat if key already exists when $flat is true.
     *
     * @param  array      &$array
     * @param  int|string  $key
     * @param  mixed       $value
     * @param  flat        $flat
     * @return array
     * @since  5.7
     */
    public static function add(array &$array, int|string $key, mixed $value, bool $flat = true): array
    {
        if ($flat && isset($array[$key])) {
            $array[$key] = self::flat([$array[$key], $value]);
        } else {
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Bridge method to add() for multiple items.
     *
     * @param  array &$array
     * @param  array  $items
     * @param  bool   $flat
     * @return array
     * @since  5.7
     */
    public static function addAll(array &$array, array $items, bool $flat = true): array
    {
        foreach ($items as $key => $value) {
            self::add($array, $key, $value, $flat);
        }

        return $array;
    }

    /**
     * Remove an item from given array by a key.
     *
     * @param  array      &$array
     * @param  int|string  $key
     * @return array
     * @since  4.0
     */
    public static function remove(array &$array, int|string $key): array
    {
        self::pull($array, $key);

        return $array;
    }

    /**
     * Bridge method to remove() for multiple items.
     *
     * @param  array             &$array
     * @param  array<int|string>  $keys
     * @return array
     * @since  4.0
     */
    public static function removeAll(array &$array, array $keys): array
    {
        self::pullAll($array, $keys);

        return $array;
    }

    /**
     * Get one/multi items from given array randomly.
     *
     * @param  array  &$array
     * @param  int     $limit
     * @param  bool    $pack
     * @param  bool    $drop
     * @return any|null
     * @since  4.12
     */
    public static function getRandom(array &$array, int $limit = 1, bool $pack = false, bool $drop = false)
    {
        return self::random($array, $limit, $pack, $drop);
    }

    /**
     * Pull one/multi items from given array randomly.
     *
     * @param  array  &$array
     * @param  int     $limit
     * @param  bool    $pack
     * @return any|null
     * @since  4.12
     */
    public static function pullRandom(array &$array, int $limit = 1, bool $pack = false)
    {
        return self::random($array, $limit, $pack, true);
    }

    /**
     * Pull one/multi items from given array randomly.
     *
     * @param  array  &$array
     * @param  int     $limit
     * @return any|null
     * @since  4.12
     */
    public static function removeRandom(array &$array, int $limit = 1): array
    {
        self::random($array, $limit, false, true);

        return $array;
    }

    /**
     * Compose an array with given keys/values, unlike errorizing array_combine() when keys/values count
     * not match.
     *
     * @param  array    $keys
     * @param  array    $values
     * @param  any|null $default
     * @return array
     * @since  4.11
     */
    public static function compose(array $keys, array $values, $default = null): array
    {
        $ret = [];

        foreach ($keys as $i => $key) {
            $ret[$key] = $values[$i] ?? $default;
        }

        return $ret;
    }

    /**
     * Merge all given item(s).
     *
     * @param  array    $array
     * @param  mixed    $item
     * @param  mixed ...$items
     * @return array
     * @since  5.30
     */
    public static function concat(array $array, mixed $item, mixed ...$items): array
    {
        // Note: Array $item won't be merged.

        return array_merge($array, [$item], $items);
    }

    /**
     * Merge all given array(s) returning a unique'd array with strict comparison.
     *
     * @param  array    $array1
     * @param  array    $array2
     * @param  array ...$arrays
     * @return array
     * @since  5.0
     */
    public static function union(array $array1, array $array2, array ...$arrays): array
    {
        $ret = [];

        foreach (array_merge($array1, $array2, ...$arrays) as $key => $value) {
            in_array($value, $ret, true) || $ret[$key] = $value;
        }

        return $ret;
    }

    /**
     * Get "really" unique items with strict comparison as default since array_unique()
     * comparison non-strict (eg: try [1,'1'])).
     *
     * @param  array $array
     * @param  bool  $strict
     * @return 5.22, 5.25 Renamed as unique() => dedupe().
     */
    public static function dedupe(array $array, bool $strict = true): array
    {
        $ret = [];

        foreach ($array as $key => $value) {
            in_array($value, $ret, $strict) || $ret[$key] = $value;
        }

        return $ret;
    }

    /**
     * Test, like JavaScript Array.some().
     *
     * @param  array    $array
     * @param  callable $func
     * @return bool
     */
    public static function test(array $array, callable $func): bool
    {
        foreach ($array as $key => $value) {
            if ($func($value, $key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Test all, like JavaScript Array.every().
     *
     * @param  array    $array
     * @param  callable $func
     * @return bool
     */
    public static function testAll(array $array, callable $func): bool
    {
        foreach ($array as $key => $value) {
            if (!$func($value, $key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Find first item that satisfies given test function.
     *
     * @param  array    $array
     * @param  callable $func
     * @param  bool     $reverse
     * @return mixed|null
     * @since  4.10
     */
    public static function find(array $array, callable $func, bool $reverse = false): mixed
    {
        $reverse && $array = array_reverse($array);

        foreach ($array as $key => $value) {
            if ($func($value, $key)) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Find all items that satisfy given test function.
     *
     * @param  array    $array
     * @param  callable $func
     * @param  bool     $reverse
     * @param  bool     $keepKeys
     * @return array<mixed|null>
     * @since  4.10
     */
    public static function findAll(array $array, callable $func, bool $reverse = false, bool $keepKeys = true): array
    {
        $reverse && $array = array_reverse($array);

        $ret = [];
        foreach ($array as $key => $value) {
            if ($func($value, $key)) {
                $keepKeys ? $ret[$key] = $value
                          : $ret[] = $value;
            }
        }
        return $ret;
    }

    /**
     * Find first item key that satisfies given test function.
     *
     * @param  array    $array
     * @param  callable $func
     * @param  bool     $reverse
     * @return int|string|null
     * @since  5.31
     */
    public static function findKey(array $array, callable $func, bool $reverse = false): int|string|null
    {
        $reverse && $array = array_reverse($array);

        foreach ($array as $key => $value) {
            if ($func($value, $key)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Find all keys that satisfy given test function.
     *
     * @param  array    $array
     * @param  callable $func
     * @param  bool     $reverse
     * @return array<int|string|null>
     * @since  5.31
     */
    public static function findKeys(array $array, callable $func, bool $reverse = false): array
    {
        $reverse && $array = array_reverse($array);

        $ret = [];
        foreach ($array as $key => $value) {
            if ($func($value, $key)) {
                $ret[] = $key;
            }
        }
        return $ret;
    }

    /**
     * Swap two keys on given array.
     *
     * @param  array      &$array
     * @param  int|string  $oldKey
     * @param  int|string  $newKey
     * @return array
     * @since  4.2
     */
    public static function swap(array &$array, int|string $oldKey, int|string $newKey): array
    {
        if (array_key_exists($oldKey, $array)) {
            $array[$newKey] = $array[$oldKey];
            unset($array[$oldKey]);
        }

        return $array;
    }

    /**
     * Swap two values on given array.
     *
     * @param  array &$array
     * @param  mixed  $oldValue
     * @param  mixed  $newValue
     * @return array
     */
    public static function swapValue(array &$array, mixed $oldValue, mixed $newValue): array
    {
        if (array_value_exists($oldValue, $array, key: $key)) {
            $array[$key] = $newValue;
        }

        return $array;
    }

    /**
     * Randomize given array, optionally returning as [key,value] pairs.
     *
     * @param  array &$array
     * @param  int    $limit
     * @param  bool   $pack
     * @param  bool   $drop
     * @return mixed|null
     */
    public static function random(array &$array, int $limit = 1, bool $pack = false, bool $drop = false): mixed
    {
        $count = count($array);
        if ($count == 0) {
            return null;
        }

        // Prevent trivial corruption from limit errors, but notice.
        if ($limit < 1) {
            throw new ValueError(sprintf(
                '%s(): Minimum limit must be 1, %s given', $limit
            ));
        } elseif ($limit > $count) {
            throw new ValueError(sprintf(
                '%s(): Maximum limit must not be greater than %s, given limit %s is exceeding '.
                'count of given array(%s)', __method__, $count, $limit, $count
            ));
        }

        $ret = [];

        srand(); // Ensure a new seed (@see https://wiki.php.net/rfc/object_scope_prng).

        // Get & arrayify single keys (limit=1).
        $keys = (array) array_rand($array, $limit);

        foreach ($keys as $key) {
            !$pack ? $ret[] = $array[$key]
                   : $ret[$key] = $array[$key];

            // Drop used item.
            if ($drop) {
                unset($array[$key]);
            }
        }

        // Assign return by pack option.
        if (count($ret) == 1) {
            $ret = !$pack ? current($ret)
                          : [key($ret), current($ret)];
        }

        return $ret;
    }

    /**
     * Shuffle given array, keeping keys as default.
     *
     * @param  array $array
     * @param  bool  $assoc
     * @return array
     */
    public static function shuffle(array $array, bool $assoc = false): array
    {
        srand(); // Ensure a new seed (@see https://wiki.php.net/rfc/object_scope_prng).

        if (!$assoc) {
            shuffle($array);
        } else {
            $keys = array_keys($array);
            shuffle($keys);

            $temp = [];
            foreach ($keys as $key) {
                $temp[$key] = $array[$key];
            }
            $array = $temp;

            // Nope.. (cos killing speed and also randomness).
            // uasort($array, function () {
            //     return rand(-1, 1);
            // });
        }

        return $array;
    }

    /**
     * Filter given array including given keys.
     *
     * @param  array             $array
     * @param  array<int|string> $keys
     * @return array
     */
    public static function include(array $array, array $keys): array
    {
        return array_filter($array, fn($key) => in_array($key, $keys, true), 2);
    }

    /**
     * Filter given array excluding given keys.
     *
     * @param  array             $array
     * @param  array<int|string> $keys
     * @return array
     */
    public static function exclude(array $array, array $keys): array
    {
        return array_filter($array, fn($key) => !in_array($key, $keys, true), 2);
    }

    /**
     * Clean given array filtering null, "" and [] values.
     *
     * @param  array      $array
     * @param  bool       $keepKeys
     * @param  array|null $ignoredKeys
     * @return array
     * @since  4.0
     */
    public static function clean(array $array, bool $keepKeys = true, array $ignoredKeys = null): array
    {
        $func = self::toFilterFunction();

        if (!$ignoredKeys) {
            $ret = array_filter($array, $func);
        } else {
            $ret = array_filter($array, fn($value, $key) => (
                in_array($key, $ignoredKeys, true) || $func($value)
            ), 1);
        }

        $keepKeys || $ret = array_values($ret);

        return $ret;
    }

    /**
     * Clear given array filtering given values.
     *
     * @param  array      $array
     * @param  array      $values
     * @param  bool       $keepKeys
     * @param  array|null $ignoredKeys
     * @return array
     * @since  6.0
     */
    public static function clear(array $array, array $values, bool $keepKeys = true, array $ignoredKeys = null): array
    {
        $func = self::toFilterFunction($values);

        if (!$ignoredKeys) {
            $ret = array_filter($array, $func);
        } else {
            $ret = array_filter($array, fn($value, $key) => (
                in_array($key, $ignoredKeys, true) || $func($value)
            ), 1);
        }

        $keepKeys || $ret = array_values($ret);

        return $ret;
    }

    /**
     * Flat given array.
     *
     * @param  array $array
     * @param  bool  $keepKeys
     * @param  bool  $fixKeys
     * @param  bool  $multi
     * @return array
     * @since  4.0
     */
    public static function flat(array $array, bool $keepKeys = false, bool $fixKeys = false, bool $multi = true): array
    {
        $ret = [];

        if ($multi) {
            $i = 0;
            // Seems short functions (=>) not work here [ref (&) issue].
            array_walk_recursive($array, function ($value, $key) use (&$ret, &$i, $keepKeys, $fixKeys) {
                !$keepKeys ? $ret[] = $value : (
                    !$fixKeys ? $ret[$key] = $value // Use original keys.
                              : $ret[is_string($key) ? $key : $i++] = $value // Re-index integer keys.
                );
            });
        } else {
            $ret = array_merge(...array_map(fn($value) => (array) $value, $array));
        }

        return $ret;
    }

    /**
     * Compact given keys with given vars.
     *
     * @param  int|string|array    $keys
     * @param  mixed            ...$vars
     * @return array
     * @since  6.0
     */
    public static function compact(int|string|array $keys, mixed ...$vars): array
    {
        return array_compact($keys, ...$vars);
    }

    /**
     * Extract given keys to given vars with refs.
     *
     * @param  int|string|array     $keys
     * @param  mixed            &...$vars
     * @return int
     * @since  6.0
     */
    public static function extract(array $array, int|string|array $keys, mixed &...$vars): int
    {
        return array_extract($array, $keys, ...$vars);
    }

    /**
     * Check whether given keys exist in given array.
     *
     * @param  array             $array
     * @param  array<int|string> $keys
     * @return bool
     */
    public static function keysExist(array $array, array $keys): bool
    {
        return array_contains_key($array, ...$keys);
    }

    /**
     * Check whether given values exist given array.
     *
     * @param  array      $array
     * @param  array<any> $values
     * @return bool
     */
    public static function valuesExist(array $array, array $values): bool
    {
        return array_contains($array, ...$values);
    }

    /**
     * Search given value's key.
     *
     * @param  array  $array
     * @param  mixed  $value
     * @param  bool   $strict
     * @param  bool   $last
     * @return int|string|null
     * @since  5.3
     */
    public static function searchKey(array $array, mixed $value, bool $strict = true, bool $last = false): int|string|null
    {
        return array_search_key($array, $value, $strict, $last);
    }

    /**
     * Search given value's last key.
     *
     * @param  array  $array
     * @param  mixed  $value
     * @param  bool   $strict
     * @return int|string|null
     * @since  5.5
     */
    public static function searchLastKey(array $array, mixed $value, bool $strict = true): int|string|null
    {
        return array_search_key($array, $value, $strict, true);
    }

    /**
     * Search given values returning found their keys.
     *
     * @param  array $array
     * @param  array $values
     * @param  bool  $strict
     * @param  bool  $reverse
     * @return array<int|string|null>
     * @since  4.0
     */
    public static function searchKeys(array $array, array $values, bool $strict = true, bool $reverse = false): array
    {
        return array_search_keys($array, $values, $strict, $reverse);
    }

    /**
     * Convert key cases to lower.
     *
     * @param  array  $array
     * @param  bool   $recursive
     * @return array
     * @since  6.0
     */
    public static function lowerKeys(array $array, bool $recursive = false): array
    {
        return array_lower_keys($array, $recursive);
    }

    /**
     * Convert key cases to upper.
     *
     * @param  array  $array
     * @param  bool   $recursive
     * @return array
     * @since  6.0
     */
    public static function upperKeys(array $array, bool $recursive = false): array
    {
        return array_upper_keys($array, $recursive);
    }

    /**
     * Convert key cases to given case.
     *
     * @param  array       $array
     * @param  int         $case
     * @param  string|null $exploder
     * @param  string|null $imploder
     * @param  bool        $recursive
     * @return array
     * @since  6.0
     */
    public static function convertKeys(array $array, int $case, string $exploder = null, string $imploder = null, bool $recursive = false): array
    {
        return array_convert_keys($array, $case, $exploder, $imploder, $recursive);
    }

    /**
     * Ensure given keys with/without given default.
     *
     * @param  array      $array
     * @param  array      $keys
     * @param  mixed|null $default
     * @return array
     * @since  4.0
     */
    public static function default(array $array, array $keys, mixed $default = null): array
    {
        return array_replace(array_fill_keys($keys, $default), $array);
    }

    /**
     * Make an options array with/without defaults.
     *
     * @param  array|null $options
     * @param  array|null $defaults
     * @param  bool       $recursive
     * @return array
     * @since  5.44
     */
    public static function options(array|null $options, array|null $defaults = null, bool $recursive = true): array
    {
        return $recursive ? array_replace_recursive((array) $defaults, (array) $options)
                          : array_replace((array) $defaults, (array) $options);
    }

    /**
     * Get first item from given array.
     *
     * @param  array $array
     * @return any|null
     */
    public static function first(array $array)
    {
        return array_first($array);
    }

    /**
     * Get last item from given array.
     *
     * @param  array $array
     * @return any|null
     */
    public static function last(array $array)
    {
        return array_last($array);
    }

    /**
     * Apply a regular/callback sort on given array.
     *
     * @param  array             $array
     * @param  callable|int|null $func
     * @param  int               $flags
     * @param  bool|null         $assoc
     * @return array
     * @since  5.3
     */
    public static function sort(array $array, callable|int $func = null, int $flags = 0, bool $assoc = null): array
    {
        $func = self::toSortFunction($func);
        $assoc ??= self::isAssoc($array);

        if ($assoc) {
            $func ? uasort($array, $func) : asort($array, $flags);
        } else {
            $func ? usort($array, $func) : sort($array, $flags);
        }

        return $array;
    }

    /**
     * Apply a regular/callback key sort on given array.
     *
     * @param  array             $array
     * @param  callable|int|null $func
     * @param  int               $flags
     * @return array
     * @since  5.3
     */
    public static function sortKey(array $array, callable|int $func = null, int $flags = 0): array
    {
        $func = self::toSortFunction($func);

        $func ? uksort($array, $func) : ksort($array, $flags);

        return $array;
    }

    /**
     * Apply a locale sort on given array.
     *
     * @param  array       $array
     * @param  string|null $locale
     * @param  bool|null   $assoc
     * @return array
     * @since  5.3 Moved from collection.Collection.
     */
    public static function sortLocale(array $array, string $locale = null, bool $assoc = null): array
    {
        $assoc ??= self::isAssoc($array);

        // Use current locale.
        if (!$locale) {
            $assoc ? uasort($array, 'strcoll') : usort($array, 'strcoll');
        } else {
            // Get & cache.
            static $currentLocale;
            $currentLocale ??= getlocale(LC_COLLATE);

            // Should change?
            if ($locale !== $currentLocale) {
                setlocale(LC_COLLATE, $locale);
            }

            $assoc ? uasort($array, 'strcoll') : usort($array, 'strcoll');

            // Restore (if needed).
            if ($locale !== $currentLocale && $currentLocale !== null) {
                setlocale(LC_COLLATE, $currentLocale);
            }
        }

        return $array;
    }

    /**
     * Apply a natural sort on given array.
     *
     * @param  array $array
     * @param  bool  $icase
     * @return array
     * @since  5.3 Moved from collection.Collection.
     */
    public static function sortNatural(array $array, bool $icase = false): array
    {
        $icase ? natcasesort($array) : natsort($array);

        return $array;
    }

    /**
     * Each wrapper for scoped function calls on given array or just for syntactic sugar.
     *
     * @param  array    $array
     * @param  callable $func
     * @return void
     */
    public static function each(array $array, callable $func): void
    {
        $i = 0;
        foreach ($array as $key => $value) {
            $func($value, $key, $i++, $array);
        }
    }

    /**
     * Filter.
     *
     * @param  array         $array
     * @param  callable|null $func
     * @param  bool          $recursive
     * @param  bool          $useKeys
     * @param  bool          $keepKeys
     * @return array
     */
    public static function filter(array $array, callable $func = null, bool $recursive = false, bool $useKeys = false, bool $keepKeys = true): array
    {
        $func ??= self::toFilterFunction();

        $ret = [];

        if ($recursive) {
            if ($useKeys) {
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $ret[$key] = self::filter($value, $func, true, true, $keepKeys);
                        continue;
                    }

                    $func($value, $key) && $ret[$key] = $value;
                }
            } else {
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $ret[$key] = self::filter($value, $func, true, false, $keepKeys);
                        continue;
                    }

                    $func($value) && $ret[$key] = $value;
                }
            }
        } else {
            if ($useKeys) {
                foreach ($array as $key => $value) {
                    $func($value, $key) && $ret[$key] = $value;
                }
            } else {
                foreach ($array as $key => $value) {
                    $func($value) && $ret[$key] = $value;
                }
            }
        }

        $keepKeys || $ret = array_values($ret);

        return $ret;
    }

    /**
     * Filter keys.
     *
     * @param  array    $array
     * @param  callable $func
     * @param  bool     $recursive
     * @return array
     */
    public static function filterKeys(array $array, callable $func, bool $recursive = false): array
    {
         $ret = [];

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $ret[$key] = self::filterKeys($value, $func, true);
                continue;
            }

            $func($key) && $ret[$key] = $value;
        }

        return $ret;
    }

    /**
     * Map.
     *
     * @param  array    $array
     * @param  callable $func
     * @param  bool     $recursive
     * @param  bool     $useKeys
     * @param  bool     $keepKeys
     * @return array
     */
    public static function map(array $array, callable $func, bool $recursive = false, bool $useKeys = false, bool $keepKeys = true): array
    {
        $ret = [];

        if ($recursive) {
            if ($useKeys) {
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $ret[$key] = self::map($value, $func, true, true, $keepKeys);
                        continue;
                    }

                    $ret[$key] = $func($value, $key);
                }
            } else {
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $ret[$key] = self::map($value, $func, true, false, $keepKeys);
                        continue;
                    }

                    $ret[$key] = $func($value);
                }
            }
        } else {
            if ($useKeys) {
                foreach ($array as $key => $value) {
                    $ret[$key] = $func($value, $key);
                }
            } else {
                foreach ($array as $key => $value) {
                    $ret[$key] = $func($value);
                }
            }
        }

        $keepKeys || $ret = array_values($ret);

        return $ret;
    }

    /**
     * Map keys.
     *
     * @param  array    $array
     * @param  callable $func
     * @param  bool     $recursive
     * @return array
     */
    public static function mapKeys(array $array, callable $func, bool $recursive = false): array
    {
        $ret = [];

        foreach ($array as $key => $value) {
            $key = $func($key);

            if ($recursive && is_array($value)) {
                $ret[$key] = self::mapKeys($value, $func, true);
                continue;
            }

            $ret[$key] = $value;
        }

        return $ret;
    }

    /**
     * Reduce.
     *
     * @param  array    $array
     * @param  mixed    $carry
     * @param  callable $func
     * @param  bool     $right
     * @return mixed
     */
    public static function reduce(array $array, mixed $carry, callable $func, bool $right = false): mixed
    {
        if ($right) {
            $array = array_reverse($array, true);
        }

        $ret = $carry;

        foreach ($array as $key => $value) {
            $ret = $func($ret, $value, $key);
        }

        return $ret;
    }

    /**
     * Reduce right.
     *
     * @param  array    $array
     * @param  mixed    $carry
     * @param  callable $func
     * @return mixed
     */
    public static function reduceRight(array $array, mixed $carry, callable $func): mixed
    {
        return self::reduce($array, $carry, $func, true);
    }

    /**
     * Apply.
     *
     * @param  array    $array
     * @param  callable $func
     * @param  bool     $recursive
     * @param  bool     $swap
     * @return array
     */
    public static function apply(array $array, callable $func, bool $recursive = false, bool $swap = false): array
    {
        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $array[$key] = self::apply($value, $func, $swap, true);
            } else {
                $array[$key] = $swap ? $func($key, $value) : $func($value, $key);
            }
        }

        return $array;
    }

    /**
     * Aggregate.
     *
     * Note: Carry must be ref'ed like `fn(&$carry, $value, ..) => ..` in calls.
     *
     * @param  array      $array
     * @param  callable   $func
     * @param  array|null $carry
     * @return array
     */
    public static function aggregate(array $array, callable $func, array $carry = null): array
    {
        $carry ??= [];

        foreach ($array as $key => $value) {
            // @cancel: Return can always be an array.
            // Note: when "return" not used carry must be ref'ed (eg: (&$carry, $value, ..)).
            // $ret = $func($carry, $value, $key, $array);
            // When "return" used.
            // if ($ret && is_array($ret)) {
            //     $carry = $ret;
            // }

            // Note: carry must be ref'ed (eg: (&$carry, $value, ..)).
            $func($carry, $value, $key, $array);

            $carry = (array) $carry;
        }

        return $carry;
    }

    /**
     * Average.
     *
     * @param  array $array
     * @param  bool  $zeros
     * @return float
     */
    public static function average(array $array, bool $zeros = true): float
    {
        $array = array_filter($array, fn($v) => (
            $zeros ? is_numeric($v) : is_numeric($v) && ($v > 0)
        ));

        return $array ? fdiv(array_sum($array), count($array)) : 0.0;
    }

    /**
     * Get an item as given type.
     *
     * @param  array      &$array
     * @param  int|string  $key
     * @param  string      $type
     * @param  any|null    $default
     * @param  bool        $drop
     * @return any
     */
    public static function getAs(array &$array, int|string $key, string $type, $default = null, bool $drop = false)
    {
        $value = self::get($array, $key, $default, $drop);
        settype($value, $type);

        return $value;
    }

    /**
     * Get an item as int.
     *
     * @param  array      &$array
     * @param  int|string  $key
     * @param  int|null    $default
     * @param  bool        $drop
     * @return int
     */
    public static function getInt(array &$array, int|string $key, int $default = null, bool $drop = false): int
    {
        return (int) self::get($array, $key, $default, $drop);
    }

    /**
     * Get an item as float.
     *
     * @param  array      &$array
     * @param  int|string  $key
     * @param  float|null  $default
     * @param  bool        $drop
     * @return float
     */
    public static function getFloat(array &$array, int|string $key, float $default = null, bool $drop = false): float
    {
        return (float) self::get($array, $key, $default, $drop);
    }

    /**
     * Get an item as string.
     *
     * @param  array       &$array
     * @param  int|string   $key
     * @param  string|null  $default
     * @param  bool         $drop
     * @return string
     */
    public static function getString(array &$array, int|string $key, string $default = null, bool $drop = false): string
    {
        return (string) self::get($array, $key, $default, $drop);
    }

    /**
     * Get an item as bool.
     *
     * @param  array      &$array
     * @param  int|string  $key
     * @param  bool|null   $default
     * @param  bool        $drop
     * @return bool
     */
    public static function getBool(array &$array, int|string $key, bool $default = null, bool $drop = false): bool
    {
        return (bool) self::get($array, $key, $default, $drop);
    }

    /**
     * Default filter function.
     */
    private static function toFilterFunction(array $values = null): callable
    {
        // Default filter values.
        $values ??= [null, "", []];

        return fn($value) => !in_array($value, $values, true);
    }

    /**
     * Sort function preparer.
     */
    private static function toSortFunction(int|callable|null $func): callable|null
    {
        // As as shortcut for reversed (-1) sorts actually.
        if (is_int($func)) {
            $func = match ($func) {
                -1      => fn($a, $b) => $a > $b ? -1 : 1,
                 1      => fn($a, $b) => $a < $b ? -1 : 1,
                default => throw new ValueError('Only 1 and -1 accepted')
            };
        }

        return $func;
    }
}
