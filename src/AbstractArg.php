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

use Arg\Contract\InvalidArgumentException;
use JsonSerializable;
use stdClass;
use TypeError;

/**
 * 参数描述类的基类
 */
abstract class AbstractArg implements JsonSerializable
{
    /**
     * static类的反射信息
     * 该属性必须是只读的，如果子类对该属性做变更，则必须进行克隆
     * @var ArgInfo
     */
    #[IgnoreAttr]
    protected ArgInfo $argInfo;
    /**
     * 记录每个字段是否赋值
     * @var array<string, bool>
     */
    #[IgnoreAttr]
    protected array $assignInfo = [];

    /**
     * @param array $parameter
     * @throws InvalidArgumentException
     */
    public function __construct(array $parameter)
    {
        $this->argInfo = ArgInfoFactory::get(static::class);
        $this->assign($parameter);
        $this->initExtendBaseArg($parameter);
    }

    /**
     * @param array $parameter
     * @return void
     * @throws InvalidArgumentException
     */
    private function initExtendBaseArg(array $parameter): void
    {
        foreach ($this->argInfo->getProperties() as $property) {
            if ($property->extendBaseArg) {
                if (array_key_exists($property->property->getName(), $parameter)) {
                    $classParameter = $parameter[$property->property->getName()];
                    if (!is_array($classParameter)) {
                        throw new InvalidArgumentException(sprintf('property %s must be array', $property->property->getName()));
                    }
                    $this->assignInfo[$property->property->getName()] = true;
                } else {
                    $classParameter = [];
                    $this->assignInfo[$property->property->getName()] = false;
                }
                $class = $property->defaultValue;
                if ($property->setter) {
                    //调用setter方法
                    call_user_func_array([$this, $property->setter], [$classParameter]);
                } else {
                    //直接赋值
                    $this->{$property->property->getName()} = new $class($classParameter);
                }
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
     * @throws InvalidArgumentException
     */
    protected function assign(array $parameter): void
    {
        foreach ($this->argInfo->getProperties() as $property) {
            if ($property->extendBaseArg) {
                continue;
            }
            $this->assignInfo[$property->property->getName()] = false;
            if (array_key_exists($property->property->getName(), $parameter)) {
                //存在需要注入的数据
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
                    $this->assignInfo[$property->property->getName()] = true;
                } catch (TypeError $error) {
                    throw new InvalidArgumentException($error->getMessage());
                }
            } else if ($property->property->isInitialized($this) === false) {
                //初始化还未初始化的属性
                if ($property->defaultValue instanceof StdClass) {
                    $this->{$property->property->getName()} = new stdClass();
                } else {
                    $this->{$property->property->getName()} = $property->defaultValue;
                }
            }
        }
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

    /**
     * 验证参数
     * @return mixed
     */
    abstract public function validate(): mixed;
}
