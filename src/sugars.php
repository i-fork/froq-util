<?php
/**
 * MIT License <https://opensource.org/licenses/mit>
 *
 * Copyright (c) 2015 Kerem Güneş
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
declare(strict_types=1);

use froq\util\{Strings, Objects};

/**
 * Strsrc & strisrc (the ever most most most wanted functions..).
 * @param  string $str
 * @param  string $src
 * @return bool
 * @since  4.0
 */
function strsrc(string $str, string $src, int $offset = 0): bool
{
    return strpos($str, $src, $offset) !== false;
}
function strisrc(string $str, string $src, int $offset = 0): bool
{
    return stripos($str, $src, $offset) !== false;
}

// @TODO use blue colors for these functions.
//
// RFC: https://wiki.php.net/rfc/str_contains
// str_contains

// RFC: https://wiki.php.net/rfc/add_str_begin_and_end_functions
// str_starts_with, stri_starts_with, str_ends_with, stri_ends_with
// str_startswith, stri_startswith, str_endswith, stri_endswith
// strsw, strisw, strew, striew

/**
 * Constant exists.
 * @param  object|string $class
 * @param  string        $name
 * @return ?bool
 * @since  4.0
 */
function constant_exists($class, string $name): ?bool
{
    return Objects::hasConstant($class, $name);
}

/**
 * Get class constants.
 * @param  string|object $class
 * @param  bool          $with_names
 * @param  bool          $with_scope_check
 * @return ?array
 * @since  4.0
 */
function get_class_constants($class, bool $with_names = true, bool $with_scope_check = true): ?array
{
    $all = false;
    if ($with_scope_check) {
        $callerClass =@ debug_backtrace(2, 2)[1]['class'];
        if ($callerClass) {
            $all = ($callerClass === (is_object($class) ? get_class($class) : $class));
        }
    }
    return Objects::getConstantValues($class, $all, $with_names);
}

/**
 * Get class properties.
 * @param  string|object $class
 * @param  bool          $with_names
 * @param  bool          $with_scope_check
 * @return ?array
 * @since  4.0
 */
function get_class_properties($class, bool $with_names = true, bool $with_scope_check = true): ?array
{
    $all = false;
    if ($with_scope_check) {
        $callerClass =@ debug_backtrace(2, 2)[1]['class'];
        if ($callerClass) {
            $all = ($callerClass === (is_object($class) ? get_class($class) : $class));
        }
    }
    return Objects::getPropertyValues($class, $all, $with_names);
}
