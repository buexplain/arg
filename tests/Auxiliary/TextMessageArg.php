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

namespace ArgTest\Auxiliary;

use Arg\ArgAttr;
use Arg\BaseArg;

/**
 * 文本消息
 */
class TextMessageArg extends BaseArg
{
    /**
     * @var string
     */
    #[ArgAttr('required', '请输入若干文字')]
    #[ArgAttr('string')]
    public string $text;

    public function __construct(array $parameter)
    {
        parent::__construct($parameter);
        $this->getArgInfo()->setRules('text', 'max:120');
    }
}