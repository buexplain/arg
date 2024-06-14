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

use Arg\Attr\IgnoreInitAttr;
use Arg\BaseArgForHyperf;
use ArgTest\Auxiliary\DefaultValueArg;
use PHPUnit\Framework\TestCase;

/**
 * 忽略注入数据特性的测试
 */
class IgnoreAssignAttrTest extends TestCase
{
    /**
     * @return void
     */
    public function testIgnoreAssignAttr()
    {
        $testData = [
            'color' => 'red',
            'width' => '400px',
            'height' => '600px',
            'arg' => ['string' => '元婴期'],
        ];
        $v = new class($testData) extends BaseArgForHyperf {
            public function __construct($testData)
            {
                parent::__construct($testData);
                $arg = $testData['arg'];
                $arg['string'] .= '、化神期';
                $this->arg = new DefaultValueArg($arg);
            }

            #[IgnoreInitAttr]
            public string $color = 'blue';
            public string $width;
            public string $height;
            #[IgnoreInitAttr]
            public DefaultValueArg $arg;
        };
        $this->assertTrue($testData['width'] == $v->width);
        $this->assertTrue($testData['height'] == $v->height);
        $this->assertFalse($testData['color'] == $v->color);
        $this->assertFalse($testData['arg']['string'] == $v->arg->string);
    }
}