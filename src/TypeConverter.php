<?php
/**
 * Copyright 2023 buexplain@qq.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Arg;

/**
 * 类型转换器
 */
class TypeConverter
{
    protected static array $methods = [
        'int' => 'toInt',
        'integer' => 'toInt',

        'float' => 'toFloat',
        'double' => 'toFloat',

        'string' => 'toString',
        'bool' => 'toBool',
        'boolean' => 'toBool',
        'array' => 'toArray',
        'object' => 'toObject',
    ];

    public static function toType(mixed &$var, string $type): bool
    {
        if (isset(self::$methods[$type])) {
            $method = self::$methods[$type];
            return self::$method($var);
        }
        return false;
    }

    /**
     * 将变量转换为整型
     * @param mixed $var
     * @return bool
     */
    protected static function toInt(mixed &$var): bool
    {
        $var = (int)$var;
        return true;
    }

    /**
     * 将变量转换为浮点型
     * @param mixed $var
     * @return bool
     */
    protected static function toFloat(mixed &$var): bool
    {
        $var = (float)$var;
        return true;
    }

    /**
     * 将变量转换为字符串
     * @param mixed $var
     * @return bool
     */
    protected static function toString(mixed &$var): bool
    {
        $var = (string)$var;
        return true;
    }

    /**
     * 将变量转换为布尔值
     * @param mixed $var
     * @return bool
     */
    protected static function toBool(mixed &$var): bool
    {
        $var = filter_var($var, FILTER_VALIDATE_BOOLEAN);
        return true;
    }

    /**
     * 将变量转换为数组
     * @param mixed $var
     * @return bool
     */
    protected static function toArray(mixed &$var): bool
    {
        $var = (array)$var;
        return true;
    }

    /**
     * 将变量转换为对象
     * @param mixed $var
     * @return bool
     */
    protected static function toObject(mixed &$var): bool
    {
        $var = (object)$var;
        return true;
    }
}