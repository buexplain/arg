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

class ArgInfo
{
    /**
     * @var array 属性的类型
     */
    protected array $types = [];

    /**
     * @var array 属性的校验规则
     */
    protected array $rules = [];

    /**
     * @var array 属性的校验规则对应的错误时提示信息
     */
    protected array $messages = [];

    /**
     * @var array 属性的setter方法
     */
    protected array $setter = [];

    /**
     * @var array 属性的getter方法
     */
    protected array $getter = [];

    /**
     * @var array 属性的类型是继承了Arg类的类
     */
    protected array $otherArg = [];


    public function getTypes(): array
    {
        return $this->types;
    }

    public function setTypes(string $property, string $type): void
    {
        $this->types[$property][] = $type;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(string $property, mixed $rule): void
    {
        $this->rules[$property][] = $rule;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(string $property, string $rule, string $message): void
    {
        $this->messages[$property . '.' . $rule] = $message;
    }

    public function getSetter(string $property): string
    {
        return $this->setter[$property];
    }

    public function setSetter(string $property, string $setter): void
    {
        $this->setter[$property] = $setter;
    }

    public function getGetter(string $property): string
    {
        return $this->getter[$property];
    }

    public function setGetter(string $property, string $getter): void
    {
        $this->getter[$property] = $getter;
    }

    public function getOtherArg(): array
    {
        return $this->otherArg;
    }

    public function setOtherArg(string $property, string $type): void
    {
        $this->otherArg[$property] = $type;
    }
}