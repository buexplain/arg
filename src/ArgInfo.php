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

use Arg\Attr\ValidationAttr;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionProperty;

/**
 * AbstractArg类的子类的属性特征集合
 */
class ArgInfo
{
    /**
     * @var array|array<string,ArgProperty>|ArgProperty[]
     */
    #[ArrayShape(['*' => ArgProperty::class])]
    protected array $properties = [];

    /**
     * @var array|array<string,array<int,string> 属性的校验规则
     */
    protected array $rules = [];

    /**
     * @var array|array<string,string> 属性的校验规则对应的错误时提示信息
     */
    protected array $messages = [];

    /**
     * @param array|array<int,ArgProperty>|ArgProperty[] $argProperty
     */
    public function __construct(array $argProperty)
    {
        foreach ($argProperty as $item) {
            $this->properties[$item->property->getName()] = $item;
            //初始化属性的校验信息
            $this->initRules($item->property);
        }
    }

    /**
     * @return array|array<string,ArgProperty>|ArgProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * 初始化属性的校验信息
     * @param ReflectionProperty $property
     * @return void
     */
    protected function initRules(ReflectionProperty $property): void
    {
        //收集每个属性的校验信息
        foreach ($property->getAttributes(ValidationAttr::class) as $attribute) {
            /**
             * @var ValidationAttr $argAttr
             */
            $argAttr = $attribute->newInstance();
            $this->setRules($property->getName(), $argAttr->rule);
            if (!is_null($argAttr->message)) {
                $this->setMessage($property->getName(), $argAttr->rule, $argAttr->message);
            }
        }
    }

    /**
     * 获取属性的校验信息
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * 设置属性的校验信息
     * @param string $propertyName
     * @param mixed $rule
     * @return void
     */
    public function setRules(string $propertyName, mixed $rule): void
    {
        //兼容字符串格式
        if (is_string($rule) && str_contains($rule, '|')) {
            $rules = explode('|', $rule);
            foreach ($rules as $rule) {
                $this->rules[$propertyName][] = $rule;
            }
            return;
        }
        $this->rules[$propertyName][] = $rule;
    }

    /**
     * 获取属性的校验信息
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * 设置属性的校验信息
     * @param string $propertyName
     * @param string $rule
     * @param string $message
     * @return void
     */
    public function setMessage(string $propertyName, string $rule, string $message): void
    {
        $this->messages[$propertyName . '.' . $rule] = $message;
    }
}