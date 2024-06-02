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
     * @var array|ArgProperty[]
     */
    protected array $properties = [];

    /**
     * @var array 属性的校验规则
     */
    protected array $rules = [];

    /**
     * @var array 属性的校验规则对应的错误时提示信息
     */
    protected array $messages = [];

    /**
     * @return array|ArgProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(ArgProperty $argProperty): void
    {
        $this->properties[$argProperty->property->getName()] = $argProperty;
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

    final public function __clone(): void
    {
        foreach ($this->properties as $index => $property) {
            $this->properties[$index] = clone $property;
        }
    }
}