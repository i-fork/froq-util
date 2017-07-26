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

namespace Froq\Util\Traits;

/**
 * @package    Froq
 * @subpackage Froq\Util\Traits
 * @object     Froq\Util\Traits\SingleTrait
 * @author     Kerem Güneş <k-gun@mail.com>
 */
trait SingleTrait
{
    // Notice: Do not define '__construct' or '__clone'
    // methods as public if you want a single use'r object.

    /**
     * Instances.
     * @var array
     */
    private static $__instances = [];

    /**
     * Forbids.
     */
    private final function __clone() {}
    private final function __construct() {}

    /**
     * Init.
     * @param  ... $arguments
     * @return object
     */
    public static final function init(...$arguments)
    {
        $className = get_called_class();
        if (!isset(self::$__instances[$className])) {
            // init without constructor
            $classInstance = (new \ReflectionClass($className))->newInstanceWithoutConstructor();

            // call constructor with initial arguments
            call_user_func_array([$classInstance, '__construct'], $arguments);

            self::$__instances[$className] = $classInstance;
        }

        return self::$__instances[$className];
    }
}

