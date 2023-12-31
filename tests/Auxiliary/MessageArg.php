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
use Arg\IgnoreAttr;

/**
 * 消息体
 */
class MessageArg extends BaseArg
{
    /**
     * 消息格式
     * @var int
     */
    #[ArgAttr('required')]
    #[ArgAttr('integer')]
    #[ArgAttr('in:1,2')]
    public int $type;

    /**
     * 消息内容
     * @var string
     */
    #[ArgAttr('required')]
    #[ArgAttr('string')]
    #[ArgAttr('json')]
    public string $content;

    /**
     * content字段实例化后的对象，无需被校验
     * @var TextMessageArg|FaceMessageArg
     */
    #[IgnoreAttr]
    public TextMessageArg|FaceMessageArg $contentObj;
}
