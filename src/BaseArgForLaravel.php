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

use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Validation\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * laravel框架的参数描述基类
 */
class BaseArgForLaravel extends AbstractArg
{
    /**
     * @param array $parameter
     * @throws InvalidArgumentException
     */
    public function __construct(array $parameter)
    {
        $this->initArgInfo();
        $this->initOrdinaryArg($parameter);
        $this->initExtendArg($parameter);
    }

    /**
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function validate(): MessageBag
    {
        //先校验普通参数
        $data = [];
        /**
         * @var ArgProperty[] $otherArg
         */
        $otherArg = [];
        foreach ($this->argInfo->getProperties() as $property) {
            if ($property->defaultArgClass) {
                $otherArg[] = $property;
            } else {
                $data[$property->property->getName()] = $this->{$property->property->getName()};
            }
        }
        /**
         * @var Factory $factory
         */
        $factory = app(Factory::class);
        /**
         * @var Validator $validator
         */
        $validator = $factory->make(
            $data,
            $this->argInfo->getRules(),
            $this->argInfo->getMessages()
        );
        //再校验Arg类型的属性
        $messageBag = $validator->getMessageBag();
        if (count($otherArg) > 0 && $messageBag->count() === 0) {
            foreach ($otherArg as $property) {
                if ($property->defaultValue === null) {
                    //如果默认值可以为null，有数据才校验
                    if ($this->initByParameter[$property->property->getName()]) {
                        $messageBag->merge($this->{$property->property->getName()}->validate());
                    }
                } else {
                    //否则直接校验
                    $messageBag->merge($this->{$property->property->getName()}->validate());
                }
                //如果校验失败，则跳出循环
                if ($messageBag->count() > 0) {
                    break;
                }
            }
        }
        return $messageBag;
    }
}
