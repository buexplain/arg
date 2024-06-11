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
use PHPUnit\Framework\TestCase;

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
}