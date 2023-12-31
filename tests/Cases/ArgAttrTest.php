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

use Arg\ArgAttr;
use PHPUnit\Framework\TestCase;

class ArgAttrTest extends TestCase
{
    /**
     * @return void
     */
    public function testArgAttr()
    {
        $test = [
            ['int', '必须是一个整数'],
            ['array', null]
        ];
        foreach ($test as $item) {
            $at = new ArgAttr($item[0], $item[1]);
            $this->assertTrue($at->rule === $item[0]);
            $this->assertTrue($at->message === $item[1]);
        }
    }
}