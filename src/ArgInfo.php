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

use JetBrains\PhpStorm\ArrayShape;

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
     * @return array|array<string,ArgProperty>|ArgProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty(string $name): ArgProperty
    {
        return $this->properties[$name];
    }

    public function setProperties(ArgProperty $argProperty): void
    {
        $this->properties[$argProperty->property->getName()] = $argProperty;
    }

    final public function __clone(): void
    {
        foreach ($this->properties as $index => $property) {
            //这里要深度克隆所有的属性特征类，因为业务上极有可能会修改ArgProperty的校验规则，进而导致常驻内存程序的出现变量污染。
            $this->properties[$index] = clone $property;
        }
    }
}