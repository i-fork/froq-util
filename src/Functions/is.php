<?php
/**
 * Copyright (c) 2016 Kerem Güneş
 *     <k-gun@mail.com>
 *
 * GNU General Public License v3.0
 *     <http://www.gnu.org/licenses/gpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

/*** "IS" function module. ***/

/**
 * Local.
 * @return bool
 */
function is_local(): bool
{
    static $local;
    if ($local === null) {
        if (defined('local')) {
            $local = (local === true);
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $local = preg_match('~\.local$~i', $_SERVER['SERVER_NAME']);
        }
    }

    return ((bool) $local);
}

/**
 * In.
 * @param  array        $array
 * @param  string|array $values
 * @return bool
 */
function is_in(array $array, $values): bool
{
    foreach ((array) $values as $value) {
        if (in_array($value, $array)) {
            return true;
        }
    }

    return false;
}

/**
 * In key.
 * @param  array        $array
 * @param  string|array $keys
 * @return bool
 */
function is_in_key(array $array, $keys): bool
{
    foreach ((array) $keys as $key) {
        if (array_key_exists($key, $array)) {
            return true;
        }
    }

    return false;
}

/**
 * Iter.
 * @param  any $input
 * @return bool
 */
function is_iter($input): bool
{
    return is_array($input)
        || ($input instanceof \stdClass)
        || ($input instanceof \Traversable);
}

/**
 * Empty.
 * @param  array ...$inputs
 * @return bool
 */
function is_empty(...$inputs): bool
{
    foreach ($inputs as $input) {
        if (empty($input)) {
            return true;
        }
        if (is_object($input) && empty((array) $input)) {
            return true;
        }
    }

    return false;
}

/**
 * UInt.
 * @param  any $input
 * @return bool
 */
function is_uint($input): bool
{
    return (((int) $input) > 0);
}
