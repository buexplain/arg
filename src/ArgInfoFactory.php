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
use ReflectionNamedType;
use ReflectionUnionType;

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
        foreach ($ref->getProperties() as $property) {
            //跳过不需要处理的字段
            if (!empty($property->getAttributes(IgnoreAttr::class))) {
                continue;
            }
            //收集每个属性的类型
            //收集当前类的属性中类型是Arg类型的属性
            $refType = $property->getType();
            if ($refType instanceof ReflectionNamedType) {
                $argInfo->setTypes($property->getName(), $refType->getName());
                if (is_subclass_of($refType->getName(), BaseArg::class)) {
                    $argInfo->setOtherArg($property->getName(), $refType->getName());
                }
            } elseif ($refType instanceof ReflectionUnionType) {
                foreach ($refType->getTypes() as $type) {
                    $argInfo->setOtherArg($property->getName(), $type->getName());
                    if (is_subclass_of($type->getName(), self::class)) {
                        $argInfo->setOtherArg($property->getName(), $type->getName());
                    }
                }
            }
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
            //收集每个属性的setter方法
            $method = 'set' . implode(array_map(function ($word) {
                    return ucfirst($word);
                }, explode('_', $property->getName())));
            if (method_exists(static::class, $method)) {
                $argInfo->setSetter($property->getName(), $method);
            } else {
                $argInfo->setSetter($property->getName(), '');
            }
        }
        return $argInfo;
    }
}