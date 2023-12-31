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
 * 发送群消息
 */
class SendGroupMessageArg extends BaseArg
{
    /**
     * @var int 群id
     */
    #[ArgAttr('required', '请输入群id')]
    #[ArgAttr('integer')]
    public int $group_id;

    /**
     * @var string 发送消息的人
     */
    #[ArgAttr('required')]
    #[ArgAttr('string')]
    public string $sender;

    /**
     * @var MessageArg 发送的消息
     */
    #[ArgAttr('required')]
    #[ArgAttr('array', '数组，必须是数组，请注意，我只接收数组！！！')]
    public MessageArg $message;
}
