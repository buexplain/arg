<?php

namespace ArgTest\Cases;

use ArgTest\Auxiliary\GSetterArg;
use PHPUnit\Framework\TestCase;

class GSetterArgTest extends TestCase
{
    public function testGSetterArg()
    {
        $t = new GSetterArg([]);
        $t->data = 'data';
        $testData = json_decode(json_encode($t), true);
        $this->assertNotEquals($testData['data'], $t->data);
        $testData = ['data' => 'data'];
        $this->assertNotEquals($testData['data'], $t->data);
    }
}