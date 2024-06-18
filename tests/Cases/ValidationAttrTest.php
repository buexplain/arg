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

use Arg\Attr\ValidationAttr;
use Arg\BaseArgForHyperf;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 测试校验规则注解
 */
class ValidationAttrTest extends TestCase
{
    /**
     * @return void
     */
    public function testArgValidationAttr()
    {
        $test = [
            ['int', '必须是一个整数'],
            ['array', null]
        ];
        foreach ($test as $item) {
            $at = new ValidationAttr($item[0], $item[1]);
            $this->assertTrue($at->rule === $item[0]);
            $this->assertTrue($at->message === $item[1]);
        }
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSetMessageAndSetRules()
    {
        $v = new class([]) extends BaseArgForHyperf {
            #[ValidationAttr('required', '不能发送空白信息')]
            public string $text;
        };
        $min = '不能小于3个字符';
        $max = '不能大于10个字符';
        $v->getArgInfo()->setRules('text', 'min:3|max:10');
        $v->getArgInfo()->setMessage('text', 'min', $min);
        $v->getArgInfo()->setMessage('text', 'max', $max);
        //测试required规则
        $this->assertEmpty($v->text);
        $bag = $v->validate();
        $this->assertTrue($bag->first() === '不能发送空白信息');
        //测试min规则
        $v->text = 'a';
        $bag = $v->validate();
        $this->assertTrue($bag->first() === $min);
        //测试max规则
        $v->text = str_repeat('a', 11);
        $bag = $v->validate();
        $this->assertTrue($bag->first() === $max);
        //测试正确的值的情况
        $v->text = 'abc';
        $bag = $v->validate();
        $this->assertEmpty($bag);
    }
}