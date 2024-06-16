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

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\MessageBag;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * hyperf框架的参数描述基类
 */
class BaseArgForHyperf extends AbstractArg
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
        /**
         * @var ValidatorInterface $validator
         */
        //先校验普通参数
        $data = [];
        $rules = [];
        $messages = [];
        foreach ($this->argInfo->getProperties() as $property) {
            if (!$property->defaultArgClass) {
                $data[$property->property->getName()] = $this->{$property->property->getName()};
                $rules[$property->property->getName()] = implode('|', $property->getRules());
                $messages = array_merge($messages, $property->getMessages());
            }
        }
        $validator = ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class)->make(
            $data,
            $rules,
            $messages
        );
        //再校验Arg类型的属性
        if ($validator->passes()) {
            $messageBag = $validator->getMessageBag();
            foreach ($this->argInfo->getProperties() as $property) {
                if ($property->defaultArgClass) {
                    if ($property->defaultValue === null) {
                        //如果默认值可以为null，有数据才校验
                        if ($this->initByParameter[$property->property->getName()]) {
                            $messageBag->merge($this->{$property->property->getName()}->validate());
                        }
                    } else {
                        //否则直接校验
                        $messageBag->merge($this->{$property->property->getName()}->validate());
                    }
                }
            }
            return $messageBag;
        }
        return $validator->getMessageBag();
    }
}