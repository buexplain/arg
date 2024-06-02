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
use ReflectionProperty;
use ReflectionUnionType;
use stdClass;

class ArgProperty
{
    public ReflectionClass $class;
    public ReflectionProperty $property;

    public bool $extendBaseArg;
    public mixed $defaultValue;
    public string $setter = '';
    public string $getter = '';

    public function __construct(ReflectionClass $class, ReflectionProperty $property)
    {
        $this->class = $class;
        $this->property = $property;
        //初始化类型信息
        $refType = $property->getType();
        if ($refType instanceof ReflectionNamedType) {
            //特定类型的属性
            $this->initNamedType();
        } elseif ($refType instanceof ReflectionUnionType) {
            //联合类型的属性
            $this->initUnionType();
        }
        //初始化属性的get set 函数
        $this->initGetSet();
    }

    protected function initNamedType(): void
    {
        $refType = $this->property->getType();
        if (is_subclass_of($refType->getName(), BaseArg::class)) {
            $this->extendBaseArg = true;
            $this->defaultValue = $refType->getName();
        } else {
            $this->extendBaseArg = false;
            $this->defaultValue = $this->getDefaultValueByType($refType->getName());
        }
    }

    protected function initUnionType(): void
    {
        $refType = $this->property->getType();
        $this->extendBaseArg = false;
        $nullType = '';
        foreach ($refType->getTypes() as $type) {
            if ($this->extendBaseArg === false && is_subclass_of($type->getName(), BaseArg::class)) {
                $this->extendBaseArg = true;
                $this->defaultValue = $type->getName();
                break;
            }
            if ($type->getName() === 'null') {
                $nullType = $type->getName();
            }
        }
        if ($this->extendBaseArg === false) {
            if ($nullType) {
                $this->defaultValue = null;
            } else {
                $this->defaultValue = $this->getDefaultValueByType($refType->getTypes()[0]->getName());
            }
        }
    }

    protected function initGetSet(): void
    {
        //收集每个属性的setter、getter方法
        $ter = implode(array_map(function ($word) {
            return ucfirst($word);
        }, explode('_', $this->property->getName())));
        $setter = 'set' . $ter;
        if (method_exists($this->class->getName(), $setter)) {
            $this->setter = $setter;
        } else {
            $this->setter = '';
        }
        $getter = 'get' . $ter;
        if (method_exists($this->class->getName(), $getter)) {
            $this->getter = $getter;
        } else {
            $this->getter = '';
        }
    }

    protected function getDefaultValueByType(string $type): mixed
    {
        if ($type === 'string') {
            $value = '';
        } elseif ($type === 'integer' || $type === 'int') {
            $value = 0;
        } elseif ($type === 'double') {
            $value = 0;
        } elseif ($type === 'boolean') {
            $value = false;
        } elseif ($type === 'array') {
            $value = [];
        } elseif ($type === 'mixed') {
            $value = null;
        } elseif ($type === 'stdClass') {
            $value = new StdClass();
        } elseif ($type === 'null') {
            $value = null;
        } else {
            $value = null;
        }
        return $value;
    }
}