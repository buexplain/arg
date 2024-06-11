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

class ArgInfo
{
    /**
     * @var array|array<string,ArgProperty>
     */
    #[ArrayShape(['*' => ArgProperty::class])]
    protected array $properties = [];

    /**
     * @return array|array<string,ArgProperty>
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
            $this->properties[$index] = clone $property;
        }
    }
}