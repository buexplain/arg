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

use Arg\Attr\ValidationAttr;
use Arg\BaseArgForHyperf;

/**
 * 消息体
 */
class MessageArg extends BaseArgForHyperf
{
    /**
     * 消息格式类别
     * @var int
     */
    #[ValidationAttr('required')]
    #[ValidationAttr('in:1,2')]
    public int $type;
    /**
     * 文本消息
     * @var TextMessageArg|null
     */
    public TextMessageArg|null $textMessage;

    /**
     * 表情消息
     * @var FaceMessageArg|null
     */
    public FaceMessageArg|null $faceMessage;
}
