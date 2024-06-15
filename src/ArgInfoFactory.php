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

use Arg\Attr\IgnoreRefAttr;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionClass;
use RuntimeException;

class ArgInfoFactory
{
    /**
     * 缓存类的反射信息
     * @var array|ArgInfo[]|array<string,ArgInfo>
     */
    #[ArrayShape(['*' => ArgInfo::class])]
    protected static array $cache = [];

    /**
     * @param string $class
     * @return ArgInfo
     * @throws RuntimeException
     */
    public static function get(string $class): ArgInfo
    {
        if (isset(self::$cache[$class])) {
            return self::$cache[$class];
        }
        $argInfo = new ArgInfo();
        self::$cache[$class] = $argInfo;
        if (!class_exists($class)) {
            throw new RuntimeException(sprintf('class %s not exists', $class));
        }
        $ref = new ReflectionClass($class);
        //遍历类的所有属性
        foreach ($ref->getProperties() as $property) {
            //跳过不需要处理的字段
            if (!empty($property->getAttributes(IgnoreRefAttr::class))) {
                continue;
            }
            $argInfo->setProperties(new ArgProperty($ref, $property));
        }
        return $argInfo;
    }
}