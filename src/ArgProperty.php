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

use Arg\Attr\IgnoreInitAttr;
use Arg\Attr\IgnoreJsonSerializeAttr;
use Arg\Attr\JsonNameAttr;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use stdClass;
use Throwable;

/**
 * 属性的特征类
 * 这个类的所有数据在反射阶段初始化完成，后续的所有逻辑都不要修改这个类的数据，否则会导致变量污染
 */
class ArgProperty
{
    /**
     * @var ReflectionClass 类的反射对象
     */
    public ReflectionClass $class;
    /**
     * @var ReflectionProperty 属性的反射对象
     */
    public ReflectionProperty $property;
    /**
     * @var string 属性的名字，可通过JsonNameAttr注解进行配置
     */
    public string $name = '';
    /**
     * @var mixed|null 属性的默认值
     */
    public mixed $defaultValue = null;
    /**
     * @var string 继承了AbstractArg类的特殊属性
     */
    public string $defaultArgClass = '';
    /**
     * @var array |string[] 属性的类型
     */
    protected array $types = [];
    /**
     * @var bool 是否在json_encode的时候跳过，可通过IgnoreJsonSerializeAttr注解进行配置
     */
    public bool $ignoreJsonSerialize;
    /**
     * @var bool 是否在属性默认值初始化的时候跳过，可通过IgnoreInitAttr注解进行配置
     */
    public bool $ignoreInit;
    /**
     * @var string 属性的set方法
     */
    public string $setter = '';
    /**
     * @var string 属性的get方法
     */
    public string $getter = '';

    public function __construct(ReflectionClass $class, ReflectionProperty $property)
    {
        $this->class = $class;
        $this->property = $property;
        //初始化ignoreJson
        $this->ignoreJsonSerialize = !empty($property->getAttributes(IgnoreJsonSerializeAttr::class));
        //初始化ignoreAssign
        $this->ignoreInit = !empty($property->getAttributes(IgnoreInitAttr::class));
        //初始化名字
        $this->initName();
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

    /**
     * 初始化名字
     * @return void
     */
    protected function initName(): void
    {
        $this->name = $this->property->getName();
        foreach ($this->property->getAttributes(JsonNameAttr::class) as $attribute) {
            /**
             * @var JsonNameAttr $argAttr
             */
            $argAttr = $attribute->newInstance();
            $this->name = $argAttr->name;
        }
    }

    /**
     * 类型转换
     * @param mixed $var
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function convert(mixed $var): mixed
    {
        $exp = null;
        foreach ($this->types as $type) {
            try {
                $tmp = $var;
                if (TypeConverter::toType($tmp, $type)) {
                    return $tmp;
                }
            } catch (Throwable $throwable) {
                $exp = $throwable;
            }
        }
        if ($exp) {
            $property = sprintf('%s::$%s', $this->class->getName(), $this->property->getName());
            throw new InvalidArgumentException(sprintf('%s failed on initialization %s', $exp->getMessage(), $property));
        }
        return $var;
    }

    /**
     * 初始化特定类型的属性
     * @return void
     */
    protected function initNamedType(): void
    {
        $refType = $this->property->getType();
        $this->defaultArgClass = $this->getDefaultArgClass($refType);
        $defaultValue = $this->getDefaultValueByType($refType);
        $this->types[] = gettype($defaultValue);
        $this->defaultValue = $refType->allowsNull() ? null : $defaultValue;
    }

    /**
     * 初始化联合类型的属性
     * @return void
     */
    protected function initUnionType(): void
    {
        $refType = $this->property->getType();
        $argType = null;
        $defaultType = null;
        foreach ($refType->getTypes() as $type) {
            if (is_null($argType) && $this->getDefaultArgClass($type)) {
                $argType = $type;
            }
            is_null($defaultType) && $defaultType = $type;
            if ($type->allowsNull()) {
                $defaultType = $type;
            }
            $this->types[] = gettype($this->getDefaultValueByType($type));
        }
        $argType && $this->defaultArgClass = $argType->getName();
        $this->defaultValue = $defaultType->allowsNull() ? null : $this->getDefaultValueByType($defaultType);
    }

    /**
     * 初始化属性的get set 函数
     * @return void
     */
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

    /**
     * 判断属性是否是Arg类型，如果是，则返回该类名
     * @param ReflectionNamedType $refType
     * @return string
     */
    protected function getDefaultArgClass(ReflectionNamedType $refType): string
    {
        if (class_exists($refType->getName())) {
            if (in_array(ArgTrait::class, class_uses($refType->getName()))) {
                return $refType->getName();
            }
            foreach (class_parents($refType->getName()) as $parent) {
                if (in_array(ArgTrait::class, class_uses($parent))) {
                    return $refType->getName();
                }
            }
        }
        return '';
    }

    /**
     * 根据类型获取默认值
     * @param ReflectionNamedType $refType
     * @return mixed
     */
    protected function getDefaultValueByType(ReflectionNamedType $refType): mixed
    {
        $type = $refType->getName();
        if ($type === 'string') {
            $value = '';
        } elseif ($type === 'int' || $type === 'integer') {
            $value = 0;
        } elseif ($type === 'float' || $type === 'double') {
            $value = 0.00;
        } elseif ($type === 'bool' || $type === 'boolean') {
            $value = false;
        } elseif ($type === 'array') {
            $value = [];
        } elseif ($type === 'mixed') {
            $value = null;
        } elseif ($type === 'stdClass' || $type === 'object') {
            $value = new StdClass();
        } elseif ($type === 'NULL' || $type === 'null') {
            $value = null;
        } elseif ($this->getDefaultArgClass($refType)) {
            $value = $type;
        } else {
            $value = null;
        }
        return $value;
    }
}