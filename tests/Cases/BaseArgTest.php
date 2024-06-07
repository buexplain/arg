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

use Arg\ArgInfoFactory;
use Arg\Contract\InvalidArgumentException;
use ArgTest\Auxiliary\FaceMessageArg;
use ArgTest\Auxiliary\MessageArg;
use ArgTest\Auxiliary\SendGroupMessageArg;
use ArgTest\Auxiliary\TextMessageArg;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class BaseArgTest extends TestCase
{
    protected static array $emptyData = [];

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|InvalidArgumentException
     */
    public function testFaceMessageArg()
    {
        //先测试空的数据，进行校验，因为数据是required，所以校验肯定失败
        $face = new FaceMessageArg([]);
        $msgBag = $face->validate();
        $this->assertNotEmpty($msgBag->all());
        //构造有效数据进行校验，测试校验成功
        $face = new FaceMessageArg(['face' => ['smile.gif', '666.gif']]);
        $msgBag = $face->validate();
        $this->assertEmpty($msgBag->all());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    public function testTextMessageArg()
    {
        $text = new TextMessageArg([]);
        //直接测试空数据校验
        $msgBag = $text->validate();
        $this->assertNotEmpty($msgBag->all());
        //测试合法数据校验
        $testData = ['text' => 'a'];
        $text = new TextMessageArg($testData);
        $msgBag = $text->validate();
        $this->assertEmpty($msgBag->all());
        $this->assertTrue($text->text == $testData['text']);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    public function testMessageArg()
    {
        $testData = [
            [
                'type' => 1,
                'textMessage' => ['text' => 'a'],
            ],
            [
                'type' => 2,
                'faceMessage' => ['face' => ['a.gif', 'b.gif']],
            ]
        ];
        foreach ($testData as $datum) {
            $message = new MessageArg($datum);
            $msgBag = $message->validate();
            $this->assertEmpty($msgBag->all());
        }
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    public function testSendGroupMessageArg()
    {
        $testData = [
            'group_id' => 1,
            'sender' => '王富贵',
            'message' => [
                'type' => 2,
                'faceMessage' => ['face' => ['a.gif', 'b.gif']],
            ]
        ];
        $sendGroupMessage = new SendGroupMessageArg($testData);
        $msgBag = $sendGroupMessage->validate();
        $this->assertEmpty($msgBag->all());
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGetArgInfo()
    {
        $face = new FaceMessageArg([]);
        $new = spl_object_id($face->getArgInfo());
        $old = spl_object_id(ArgInfoFactory::get(FaceMessageArg::class));
        $this->assertTrue($old != $new);
    }
}