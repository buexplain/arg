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

use Arg\Attr\IgnoreJsonSerializeAttr;
use Arg\Attr\JsonNameAttr;
use Arg\BaseArgForHyperf;
use ArgTest\Auxiliary\TextMessageArg;
use PHPUnit\Framework\TestCase;

/**
 * 属性的json序列化测试
 */
class JsonTest extends TestCase
{
    /**
     * @return void
     */
    public function testJsonName()
    {
        $testData = [
            'last_name' => '王',
            'first_name' => '富贵',
            'text' => ['text' => '大凡物不得其平则鸣'],
        ];
        $v = new class($testData) extends BaseArgForHyperf {
            #[JsonNameAttr('last_name')]
            public string $lastName;
            #[JsonNameAttr('first_name')]
            public string $firstName;
            #[JsonNameAttr('text')]
            public TextMessageArg $textMessage;
        };
        $this->assertTrue($testData['last_name'] === $v->lastName);
        $this->assertTrue($testData['first_name'] === $v->firstName);
        $this->assertTrue($testData['text']['text'] === $v->textMessage->text);
        $this->assertTrue(json_decode(json_encode($v), true) === $testData);
    }

    /**
     * @return void
     */
    public function testIgnoreJsonSerialize()
    {
        $testData = [
            'lastName' => '王',
            'firstName' => '富贵',
        ];
        $v = new class($testData) extends BaseArgForHyperf {
            #[IgnoreJsonSerializeAttr]
            public string $lastName;
            public string $firstName;
        };
        $this->assertTrue($testData['lastName'] === $v->lastName);
        $this->assertTrue($testData['firstName'] === $v->firstName);
        $this->assertFalse(json_decode(json_encode($v), true) === $testData);
    }
}