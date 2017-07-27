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

use \stdClass as object;

/**
 * Local.
 * @return bool
 */
function is_local(): bool
{
    return (local === true);
}

/**
 * Is cli.
 * @return bool
 */
function is_cli(): bool
{
    return PHP_SAPI === 'cli';
}

/**
 * Is cli server
 * @return bool
 */
function is_cli_server(): bool
{
    return PHP_SAPI === 'cli-server';
}

/**
 * Array key.
 * @param  array $array
 * @param  any   $keys
 * @return bool
 */
function is_array_key(array $array, $keys): bool
{
    foreach ((array) $keys as $key) {
        if (!array_key_exists($key, $array)) {
            return false;
        }
    }

    return true;
}

/**
 * Array value.
 * @param  array $array
 * @param  any   $values
 * @param  bool  $strict
 * @return bool
 */
function is_array_value(array $array, $values, bool $strict = false): bool
{
    foreach ((array) $values as $value) {
        if (!in_array($value, $array, $strict)) {
            return false;
        }
    }

    return true;
}

/**
 * Iter.
 * @param  any $arg
 * @return bool
 */
function is_iter($arg): bool
{
    return is_iterable($arg) || $arg instanceof object;
}

/**
 * Set.
 * @param  any        $arg
 * @param  array|null $keys
 * @return bool
 */
function is_set($arg, array $keys = null): bool
{
    $return = isset($arg);
    if ($return && !empty($keys)) {
        if (is_array($arg)) {
            foreach ($keys as $key) {
                if (!isset($arg[$key])) return false;
            }
        } elseif ($arg instanceof object) {
            foreach ($keys as $key) {
                if (!isset($arg->{$key})) return false;
            }
        }
    }

    return $return;
}

/**
 * Empty.
 * @param  ... $args
 * @return bool
 */
function is_empty(...$args): bool
{
    foreach ($args as $arg) {
        if (empty($arg)) {
            return true;
        }
        if (is_iter($arg)) {
            $arg = (array) $arg;
            if (empty($arg)) return true;
        }
    }

    return false;
}

/**
 * Nil.
 * @param  any $arg
 * @return bool
 */
function is_nil($arg): bool
{
    return ($arg === nil);
}

/**
 * Nils.
 * @param  any $arg
 * @return bool
 */
function is_nils($arg): bool
{
    return ($arg === nils);
}
