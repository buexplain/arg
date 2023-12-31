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
use ArgTest\Auxiliary\FaceMessageArg;
use ArgTest\Auxiliary\MessageArg;
use ArgTest\Auxiliary\SendGroupMessageArg;
use ArgTest\Auxiliary\TextMessageArg;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class BaseArgTest extends TestCase
{
    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFaceMessageArg()
    {
        //先测试空的数据，进行校验，因为数据是required，所以校验肯定失败
        $face = new FaceMessageArg();
        $msgBag = $face->validate([]);
        $this->assertNotEmpty($msgBag->all());
        //构造有效数据进行校验，测试校验成功
        $testData = ['face' => ['smile.gif', '666.gif']];
        $msgBag = $face->validate($testData);
        $this->assertEmpty($msgBag->all());
        //再次校验空数据，依然失败
        $msgBag = $face->validate([]);
        $this->assertNotEmpty($msgBag->all());
        //将有效数据注入到对象中，序列化对象，比较序列化的数据与有效数据是否一致
        $face->assign($testData);
        $this->assertTrue(json_decode(json_encode($face), true) === $testData);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testTextMessageArg()
    {
        $text = new TextMessageArg();
        //直接测试空数据校验
        $msgBag = $text->validate([]);
        $this->assertNotEmpty($msgBag->all());
        //注入数据后再次测试空数据校验
        $testData = ['text' => 'a'];
        $text->assign($testData);
        $this->assertTrue($text->text == $testData['text']);
        $msgBag = $text->validate([]);
        $this->assertNotEmpty($msgBag->all());
        //测试合法数据校验
        $msgBag = $text->validate($testData);
        $this->assertEmpty($msgBag->all());
        $this->assertTrue($text->text == $testData['text']);
        //再次注入数据后判断是否符合预期
        $testData = ['text' => 'b'];
        $text->assign($testData);
        $this->assertTrue($text->text == $testData['text']);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMessageArg()
    {
        $message = new MessageArg();
        $this->assertEquals('{}', json_encode($message));
        $testData = [
            [
                'type' => 1,
                'content' => json_encode(['text' => 'a']),
            ],
            [
                'type' => 2,
                'content' => json_encode(['face' => ['a.gif', 'b.gif']]),
            ]
        ];
        foreach ($testData as $datum) {
            $msgBag = $message->validate($datum);
            $this->assertEmpty($msgBag->all());
            $message->assign($datum);
            if ($message->type === 1) {
                $content = new TextMessageArg();
            } else {
                $content = new FaceMessageArg();
                $content->getArgInfo()->setRules('face', 'max:2');
            }
            $msgBag = $content->validate(json_decode($message->content, true));
            $this->assertEmpty($msgBag->all());
            $message->contentObj = $content;
            $this->assertEquals(json_encode($datum), json_encode($message));
        }
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSendGroupMessageArg()
    {
        $sendGroupMessage = new SendGroupMessageArg();
        $this->assertEquals('{"message":{}}', json_encode($sendGroupMessage));
        $testData = [
            'group_id' => 1,
            'sender' => '王富贵',
            'message' => [
                'type' => 2,
                'content' => json_encode(['face' => ['a.gif', 'b.gif']]),
            ]
        ];
        $msgBag = $sendGroupMessage->validate($testData);
        $this->assertEmpty($msgBag->all());
        $sendGroupMessage->assign($testData);
        $this->assertEquals(json_encode($testData), json_encode($sendGroupMessage));
    }

    public function testGetArgInfo()
    {
        $text = new TextMessageArg();
        $this->assertNotEquals($text->getArgInfo()->getRules(), ArgInfoFactory::get(TextMessageArg::class)->getRules());
        $new = spl_object_id($text->getArgInfo());
        $old = spl_object_id(ArgInfoFactory::get(TextMessageArg::class));
        $this->assertTrue($old != $new);
        $this->assertTrue($new == spl_object_id($text->getArgInfo()));
        $this->assertTrue($old == spl_object_id(ArgInfoFactory::get(TextMessageArg::class)));
    }
}