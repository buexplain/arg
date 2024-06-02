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
use InvalidArgumentException;
use JsonSerializable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use TypeError;

/**
 * 参数描述类的基类
 */
class BaseArg implements JsonSerializable
{
    /**
     * static类的反射信息
     * 该属性必须是私有的，如果子类对该属性做变更，则必须进行克隆
     * @var ArgInfo
     */
    #[IgnoreAttr]
    private ArgInfo $argInfo;

    /**
     * @param array $parameter
     * @throws InvalidArgumentException
     */
    public function __construct(array $parameter)
    {
        $this->argInfo = ArgInfoFactory::get(static::class);
        $this->assign($parameter);
        foreach ($this->argInfo->getProperties() as $property) {
            if ($property->extendBaseArg) {
                $class = $property->defaultValue;
                $this->{$property->property->getName()} = new $class($parameter[$property->property->getName()] ?? []);
            }
        }
    }

    /**
     *
     * @return ArgInfo
     */
    public function getArgInfo(): ArgInfo
    {
        if (spl_object_id($this->argInfo) === spl_object_id(ArgInfoFactory::get(static::class))) {
            $this->argInfo = clone $this->argInfo;
        }
        return $this->argInfo;
    }

    /**
     * 注入数据到对象本身的属性中
     * @param array $parameter
     * @return void
     */
    protected function assign(array $parameter): void
    {
        foreach ($this->argInfo->getProperties() as $property) {
            if ($property->extendBaseArg) {
                continue;
            }
            if (isset($parameter[$property->property->getName()])) {
                $v = $parameter[$property->property->getName()];
                //优先使用setter方法进行注入
                try {
                    if ($property->setter) {
                        call_user_func_array([$this, $property->setter], [$v]);
                    } else {
                        //没有setter方法，直接赋值，这里可能会发生错误
                        if ($property->defaultValue instanceof StdClass) {
                            $this->{$property->property->getName()} = (object)$v;
                        } else {
                            $this->{$property->property->getName()} = $v;
                        }
                    }
                } catch (TypeError $error) {
                    throw new InvalidArgumentException($error->getMessage());
                }
            } else if ($property->property->isInitialized($this) === false) {
                if ($property->defaultValue instanceof StdClass) {
                    $this->{$property->property->getName()} = new stdClass();
                } else {
                    $this->{$property->property->getName()} = $property->defaultValue;
                }
            }
        }
    }

    /**
     * 根据校验规则，执行校验逻辑
     * @return MessageBag
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function validate(): MessageBag
    {
        /**
         * @var ValidatorInterface $validator
         */
        $data = [];
        foreach ($this->argInfo->getProperties() as $property) {
            if ($property->extendBaseArg === false) {
                $data[$property->property->getName()] = $this->{$property->property->getName()};
            }
        }
        $validator = ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class)->make(
            $data,
            $this->argInfo->getRules(),
            $this->argInfo->getMessages()
        );
        $messageBag = $validator->getMessageBag();
        /**
         * @var MessageBag[] $otherMessageBag
         */
        if ($validator->passes()) {
            //校验通过，继续校验本对象的Arg类型的属性
            $otherMessageBag = [];
            foreach ($this->argInfo->getProperties() as $property) {
                if ($property->extendBaseArg) {
                    $otherMessageBag[] = $this->{$property->property->getName()}->validate();
                }
            }
            foreach ($otherMessageBag as $item) {
                $messageBag->merge($item);
            }
        }
        return $messageBag;
    }

    /**
     * json序列化时，只序列化被注解的字段
     * @return stdClass
     */
    public function jsonSerialize(): stdClass
    {
        $ret = new stdClass();
        foreach ($this->argInfo->getProperties() as $property) {
            if ($property->getter) {
                $ret->{$property->property->getName()} = call_user_func_array([$this, $property->getter], []);
            } else {
                $ret->{$property->property->getName()} = $this->{$property->property->getName()};
            }
        }
        return $ret;
    }
}
