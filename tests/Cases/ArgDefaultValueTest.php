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

use ArgTest\Auxiliary\DefaultValueArg;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

/**
 * 默认值的注入测试
 */
class ArgDefaultValueTest extends TestCase
{
    public function testDefaultValue()
    {
        $arg = new class([]) extends DefaultValueArg {
            public DefaultValueArg $arg;
        };
        $err = '';
        try {
            foreach ([$arg, $arg->arg] as $item) {
                /**
                 * @var $item DefaultValueArg
                 */
                $this->assertTrue($item->string === '');
                $this->assertTrue($item->int === 0);
                $this->assertTrue($item->float === floatval(0));
                $this->assertTrue($item->bool === false);
                $this->assertTrue($item->array === []);
                $this->assertTrue($item->mixed === null);
                $this->assertTrue($item->object instanceof stdClass);
                $this->assertTrue($item->stdClass == $item->object);
            }
        } catch (Throwable $throwable) {
            $err = $throwable->getMessage();
        }
        $this->assertTrue($err === '');
    }
}