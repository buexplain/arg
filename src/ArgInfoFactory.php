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

use ReflectionClass;

class ArgInfoFactory
{
    protected static array $cache = [];

    public static function get(string $class): ArgInfo
    {
        if (isset(self::$cache[$class])) {
            return self::$cache[$class];
        }
        $argInfo = new ArgInfo();
        self::$cache[$class] = $argInfo;
        if (!class_exists($class)) {
            return $argInfo;
        }
        $ref = new ReflectionClass($class);
        //遍历类的所有属性
        foreach ($ref->getProperties() as $property) {
            //跳过不需要处理的字段
            if (!empty($property->getAttributes(IgnoreAttr::class))) {
                continue;
            }
            //收集每个属性
            $argInfo->setProperties(new ArgProperty($ref, $property));
            //收集每个属性的校验信息
            foreach ($property->getAttributes(ArgAttr::class) as $attribute) {
                /**
                 * @var ArgAttr $rule
                 */
                $argAttr = $attribute->newInstance();
                $argInfo->setRules($property->getName(), $argAttr->rule);
                if (!is_null($argAttr->message)) {
                    $argInfo->setMessages($property->getName(), $argAttr->rule, $argAttr->message);
                }
            }
        }
        return $argInfo;
    }
}