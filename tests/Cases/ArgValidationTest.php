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

namespace ArgTest\Cases;

use Arg\Attr\ArgValidationAttr;
use Arg\BaseArgForHyperf;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 测试各种校验规则
 */
class ArgValidationTest extends TestCase
{
    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAccepted()
    {
        $fun = function (array $parameter) {
            return new class($parameter) extends BaseArgForHyperf {
                #[ArgValidationAttr('accepted', '请同意服务协议')]
                public bool $agreement;
            };
        };
        $v = $fun(['agreement' => true]);
        $this->assertTrue($v->agreement);
        $bag = $v->validate();
        $this->assertEmpty($bag);
        $v = $fun(['agreement' => false]);
        $this->assertFalse($v->agreement);
        $bag = $v->validate();
        $this->assertNotEmpty($bag);
        $this->assertSame($v->getArgInfo()->getProperty('agreement')->getMessages()['agreement.accepted'], $bag->first());
        $v = $fun([]);
        $bag = $v->validate();
        $this->assertNotEmpty($bag);
        $this->assertSame($v->getArgInfo()->getProperty('agreement')->getMessages()['agreement.accepted'], $bag->first());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAcceptedIf()
    {
        $fun = function (array $parameter) {
            return new class($parameter) extends BaseArgForHyperf {
                public bool $agree;
                #[ArgValidationAttr('required')]
                #[ArgValidationAttr('accepted_if:agree,true', '请先勾选服务协议')]
                public string $terms;
            };
        };
        $v = $fun(['agree' => true, 'terms' => 'yes']);
        $this->assertTrue($v->agree);
        $this->assertSame('yes', $v->terms);
        $bag = $v->validate();
        $this->assertEmpty($bag);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testActiveUrl()
    {
        $fun = function (array $parameter) {
            return new class($parameter) extends BaseArgForHyperf {
                #[ArgValidationAttr('required')]
                #[ArgValidationAttr('active_url')]
                public string $url;
            };
        };
        $v = $fun(['url' => 'https://example.com/']);
        $bag = $v->validate();
        $this->assertEmpty($bag);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testArray()
    {
        $fun = function (array $parameter) {
            return new class($parameter) extends BaseArgForHyperf {
                #[ArgValidationAttr('required')]
                #[ArgValidationAttr('required_array_keys:foo,bar')]
                public array $arr;
            };
        };
        $v = $fun(['arr' => ['foo' => '1', 'bar' => 2]]);
        $bag = $v->validate();
        $this->assertEmpty($bag);
        $v = $fun([]);
        $bag = $v->validate();
        $this->assertNotEmpty($bag);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNullable()
    {
        $fun = function (array $parameter) {
            return new class($parameter) extends BaseArgForHyperf {
                #[ArgValidationAttr('nullable')]
                #[ArgValidationAttr('regex:/^1[3-9]\d{9}$/')]
                public string $phone;
            };
        };
        //测试没有赋值的情况下验证通过
        $v = $fun([]);
        $bag = $v->validate();
        $this->assertEmpty($bag);
        //测试赋予正确的值的情况验证通过
        $v = $fun(['phone' => '18812345678']);
        $this->assertTrue($v->phone === '18812345678');
        $bag = $v->validate();
        $this->assertEmpty($bag);
        //测试赋予错误的值的情况验证失败
        $v = $fun(['phone' => 'a']);
        $bag = $v->validate();
        $this->assertNotEmpty($bag);
    }
}