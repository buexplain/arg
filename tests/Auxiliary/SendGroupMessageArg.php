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
 * 发送群消息
 */
class SendGroupMessageArg extends BaseArgForHyperf
{
    /**
     * @var int 群id
     */
    #[ValidationAttr('required', '请输入群id')]
    #[ValidationAttr('min:1', '群id不能小于1')]
    public int $group_id;

    /**
     * @var string 发送消息的人
     */
    #[ValidationAttr('required')]
    #[ValidationAttr('max:7', '发送者名称不能超过7个字符')]
    public string $sender;

    /**
     * @var MessageArg 发送的消息
     */
    public MessageArg $message;
}
