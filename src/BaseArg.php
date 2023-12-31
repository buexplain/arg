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

use Error;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\MessageBag;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use JsonSerializable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

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
     * @param bool $initializationArgProperty 是否自动初始化子类中的继承了本类的属性
     */
    public function __construct(bool $initializationArgProperty = true)
    {
        $this->argInfo = ArgInfoFactory::get(static::class);
        if ($initializationArgProperty) {
            foreach ($this->argInfo->getOtherArg() as $property => $class) {
                $this->{$property} = new $class();
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
     * @param array $data
     * @return void
     */
    public function assign(array $data): void
    {
        foreach ($this->argInfo->getTypes() as $property => $types) {
            if (isset($data[$property])) {
                $v = $data[$property];
                //先转成目标属性的类型
                foreach ($types as $type) {
                    if (is_subclass_of($type, self::class)) {
                        //继承了本类的属性，调用其自身的注入函数
                        if (!is_array($v)) {
                            $v = json_decode($v, true);
                        }
                        $this->{$property}->assign($v);
                        continue 2;
                    } elseif ($type === 'string') {
                        $v = trim((string)$v);
                        break;
                    } elseif ($type === 'integer' || $type === 'int') {
                        $v = filter_var($v, FILTER_VALIDATE_INT);
                        break;
                    } elseif ($type === 'double') {
                        $v = filter_var($v, FILTER_VALIDATE_FLOAT);
                        break;
                    } elseif ($type === 'boolean') {
                        $v = filter_var($v, FILTER_VALIDATE_BOOLEAN);
                        break;
                    } elseif ($type === 'array') {
                        $v = (array)$v;
                        break;
                    }
                }
                //优先使用setter方法进行注入
                $method = $this->argInfo->getSetter($property);
                if ($method) {
                    call_user_func_array([$this, $method], [$v]);
                    continue;
                }
                //没有setter方法，直接赋值，这里可能会发生错误
                $this->{$property} = $v;
            }
        }
    }

    /**
     * 执行根据校验规则，执行校验逻辑
     * @param array $data 被校验的数据
     * @return MessageBag
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function validate(array $data): MessageBag
    {
        /**
         * @var ValidatorInterface $validator
         */
        $validator = ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class)->make(
            $data,
            $this->argInfo->getRules(),
            $this->argInfo->getMessages()
        );
        /**
         * @var MessageBag[] $otherMessageBag
         */
        $otherMessageBag = [];
        if ($validator->passes()) {
            //校验通过，继续校验本对象的Arg类型的属性
            foreach ($this->argInfo->getOtherArg() as $property => $class) {
                if (isset($data[$property])) {
                    $nextData = $data[$property];
                    if (!is_array($nextData)) {
                        $nextData = json_decode($nextData, true);
                    }
                    $otherMessageBag[] = $this->{$property}->validate($nextData);
                }
            }
        }
        $messageBag = $validator->getMessageBag();
        foreach ($otherMessageBag as $item) {
            $messageBag->merge($item);
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
        foreach ($this->argInfo->getTypes() as $property => $discard) {
            try {
                $ret->{$property} = $this->{$property};
            } catch (Error $throwable) {
                //如果字段未被初始化，则会报错，这里直接屏蔽错误即可
            }
        }
        return $ret;
    }
}
