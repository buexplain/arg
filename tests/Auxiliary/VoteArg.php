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
use Arg\InvalidArgumentException;

class VoteArg extends BaseArgForHyperf
{
    /**
     * @var int 投票id
     */
    #[ValidationAttr('required')]
    public int $id;
    /**
     * @var string 投票标题
     */
    #[ValidationAttr('required')]
    public string $title;

    /**
     * @var array 投票选项
     * @var VoteOptionArg[]
     */
    #[ValidationAttr('required')]
    public array $options;

    /**
     * @throws InvalidArgumentException
     */
    protected function initOptionsHook(array $data): void
    {
        foreach ($data as $item) {
            $this->options[] = new VoteOptionArg($item);
        }
    }

    protected function jsonSerializeOptionsHook(): array
    {
        $ret = [];
        foreach ($this->options as $item) {
            $ret[] = $item->jsonSerialize();
        }
        return $ret;
    }
}