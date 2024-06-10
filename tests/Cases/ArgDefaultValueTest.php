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

use Arg\AbstractArg;
use Arg\BaseArgForHyperf;
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

    /**
     * @return void
     */
    public function testDefaultNullValue()
    {
        $fun = function (array $parameter) {
            return new class($parameter) extends BaseArgForHyperf {
                public string|null $string;
                public int|null $int;
                public float|null $float;
                public bool|null $bool;
                public array|null $array;
                public stdClass|null $stdClass;
                public object|null $object;
                public DefaultValueArg|null $arg;
            };
        };
        //测试允许null的时候，所有的默认值是否为null
        $arg = $fun([]);
        $this->assertTrue(is_null($arg->string));
        $this->assertTrue(is_null($arg->int));
        $this->assertTrue(is_null($arg->float));
        $this->assertTrue(is_null($arg->bool));
        $this->assertTrue(is_null($arg->array));
        $this->assertTrue(is_null($arg->object));
        $this->assertTrue(is_null($arg->stdClass));
        $this->assertTrue(is_null($arg->arg));
        //测试允许null的时候，给定值后，所有的默认值是否为不为null
        $testData = [
            'string' => 'string',
            'int' => 100,
            'float' => 10.2,
            'bool' => true,
            'array' => ['a', 'b'],
            'object' => new stdClass(),
            'stdClass' => new stdClass(),
            'arg' => [],
        ];
        $arg = $fun($testData);
        foreach ($testData as $k => $v) {
            if ($k === 'arg') {
                $this->assertTrue($arg->arg instanceof AbstractArg);
            } else {
                $this->assertTrue($arg->{$k} === $v);
            }
        }
    }
}