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

use Arg\BaseArgForHyperf;
use PHPUnit\Framework\TestCase;

/**
 * 属性的钩子函数测试
 */
class GetSetterTest extends TestCase
{
    /**
     * @return void
     */
    public function testGSetterArg()
    {
        $testData = [
            'data' => 'data',
        ];
        $v = new class($testData) extends BaseArgForHyperf {
            public string $data;

            public function getData(): string
            {
                return $this->data . '干扰数据';
            }

            public function setData(string $data): void
            {
                $this->data = $data . '干扰数据';
            }
        };
        //测试set方法
        $this->assertNotEquals($testData['data'], $v->data);
        //测试get方法
        $v->data = $testData['data'];
        $data = json_decode(json_encode($v), true);
        $this->assertNotEquals($testData['data'], $data['data']);
    }
}