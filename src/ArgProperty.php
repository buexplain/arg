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
use Arg\Attr\ValidationAttr;
use Arg\Attr\JsonNameAttr;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use stdClass;

/**
 * 属性的特征类
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
    /**
     * @var array 属性的校验规则
     */
    protected array $rules = [];

    /**
     * @var array 属性的校验规则对应的错误时提示信息
     */
    protected array $messages = [];

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
        //初始化属性的校验信息
        $this->initRules();
    }

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

    protected function initRules(): void
    {
        //收集每个属性的校验信息
        foreach ($this->property->getAttributes(ValidationAttr::class) as $attribute) {
            /**
             * @var ValidationAttr $argAttr
             */
            $argAttr = $attribute->newInstance();
            $this->setRules($argAttr->rule);
            if (!is_null($argAttr->message)) {
                $this->setMessages($argAttr->rule, $argAttr->message);
            }
        }
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(mixed $rule): void
    {
        $this->rules[] = $rule;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(string $rule, string $message): void
    {
        $this->messages[$this->property->getName() . '.' . $rule] = $message;
    }

    protected function initNamedType(): void
    {
        $refType = $this->property->getType();
        $this->defaultArgClass = is_subclass_of($refType->getName(), AbstractArg::class) ? $refType->getName() : '';
        $this->defaultValue = $this->getDefaultValueByType($refType);
    }

    protected function initUnionType(): void
    {
        $refType = $this->property->getType();
        $argType = null;
        $defaultType = null;
        foreach ($refType->getTypes() as $type) {
            if (is_null($argType) && is_subclass_of($type->getName(), AbstractArg::class)) {
                $argType = $type;
            }
            is_null($defaultType) && $defaultType = $type;
            if ($type->allowsNull()) {
                $defaultType = $type;
            }
        }
        $argType && $this->defaultArgClass = $argType->getName();
        $this->defaultValue = $this->getDefaultValueByType($defaultType);
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

    protected function getDefaultValueByType(ReflectionNamedType $refType): mixed
    {
        if ($refType->allowsNull()) {
            return null;
        }
        $type = $refType->getName();
        if ($type === 'string') {
            $value = '';
        } elseif ($type === 'int' || $type === 'integer') {
            $value = 0;
        } elseif ($type === 'float' || $type === 'double') {
            $value = 0;
        } elseif ($type === 'bool' || $type === 'boolean') {
            $value = false;
        } elseif ($type === 'array') {
            $value = [];
        } elseif ($type === 'mixed') {
            $value = null;
        } elseif ($type === 'stdClass' || $type === 'object') {
            $value = new StdClass();
        } elseif ($type === 'null') {
            $value = null;
        } elseif (is_subclass_of($type, AbstractArg::class)) {
            $value = $type;
        } else {
            $value = null;
        }
        return $value;
    }
}