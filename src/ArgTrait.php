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

use Arg\Attr\IgnoreRefAttr;
use JetBrains\PhpStorm\ArrayShape;
use stdClass;
use TypeError;

/**
 * 参数描述类的基类
 */
trait ArgTrait
{
    /**
     * static类的反射信息
     * 该属性必须是只读的，如果子类或者外部逻辑需要对该属性做变更，则必须进行克隆操作，尤其是常驻内存的php进程
     * @var ArgInfo
     */
    #[IgnoreRefAttr]
    protected ArgInfo $argInfo;
    /**
     * @var array|array<string,bool>|bool[] 记录每个字段是否是因为外部输入参数被初始化的
     */
    #[IgnoreRefAttr]
    #[ArrayShape(['*' => 'bool'])]
    protected array $initByParameter = [];

    /**
     * 初始化static类的反射信息
     * @return void
     */
    protected function initArgInfo(): void
    {
        $this->argInfo = ArgInfoFactory::get(static::class);
    }

    /**
     * 初始化没有继承本类的普通参数
     * @param array $parameter
     * @return void
     * @throws InvalidArgumentException
     */
    protected function initOrdinaryArg(array $parameter): void
    {
        foreach ($this->argInfo->getProperties() as $property) {
            //跳过继承arg的特殊属性
            if ($property->defaultArgClass !== '') {
                continue;
            }
            //跳过忽略赋值的属性
            if ($property->ignoreInit) {
                continue;
            }
            $this->initByParameter[$property->property->getName()] = false;
            if (array_key_exists($property->name, $parameter)) {
                //存在需要注入的数据
                $v = $parameter[$property->name];
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
                    $this->initByParameter[$property->property->getName()] = true;
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
     * 初始化继承本类的特殊参数
     * @param array $parameter
     * @return void
     * @throws InvalidArgumentException
     */
    protected function initExtendArg(array $parameter): void
    {
        foreach ($this->argInfo->getProperties() as $property) {
            if ($property->defaultArgClass === '') {
                //跳过没有继承arg的普通属性
                continue;
            }
            //跳过忽略赋值的属性
            if ($property->ignoreInit) {
                continue;
            }
            //存在外部入参
            if (array_key_exists($property->name, $parameter)) {
                $classParameter = $parameter[$property->name];
                if (!is_array($classParameter)) {
                    throw new InvalidArgumentException(sprintf('parameter %s must be array', $property->name));
                }
                if ($property->setter) {
                    //调用setter方法
                    call_user_func_array([$this, $property->setter], [$classParameter]);
                } else {
                    //直接赋值
                    $class = $property->defaultArgClass;
                    $this->{$property->property->getName()} = new $class($classParameter);
                }
                //标记该属性已经因为外部参数的输入而被赋值
                $this->initByParameter[$property->property->getName()] = true;
                continue;
            }
            //不存在外部入参
            $this->initByParameter[$property->property->getName()] = false;
            if ($property->setter) {
                //调用setter方法
                call_user_func_array([$this, $property->setter], [[]]);
            } else if (is_null($property->defaultValue)) {
                $this->{$property->property->getName()} = null;
            } else {
                //直接赋值
                $class = $property->defaultArgClass;
                $this->{$property->property->getName()} = new $class([]);
            }
        }
    }

    /**
     * 获取属性的反射信息
     * @return ArgInfo
     */
    public function getArgInfo(): ArgInfo
    {
        if (spl_object_id($this->argInfo) === spl_object_id(ArgInfoFactory::get(static::class))) {
            //这里必须克隆一次，因为不知道后续的操作是否会修改argInfo
            $this->argInfo = clone $this->argInfo;
        }
        return $this->argInfo;
    }


    /**
     * json序列化
     * @return stdClass
     */
    public function jsonSerialize(): stdClass
    {
        $ret = new stdClass();
        foreach ($this->argInfo->getProperties() as $property) {
            //跳过忽略序列化的属性
            if ($property->ignoreJsonSerialize) {
                continue;
            }
            if ($property->getter) {
                $ret->{$property->name} = call_user_func_array([$this, $property->getter], []);
            } else {
                $ret->{$property->name} = $this->{$property->property->getName()};
            }
        }
        return $ret;
    }
}
