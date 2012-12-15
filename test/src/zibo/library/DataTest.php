<?php

namespace zibo\library;

use zibo\test\BaseTestCase;

class DataTest extends BaseTestCase {

    public function testOverrideSet() {
        $methodName = 'setTest';
        $dataMock = $this->getMock('zibo\\library\\Data', array($methodName));
        $dataMock
            ->expects($this->once())
            ->method($methodName);

        $dataMock->test = $methodName;
    }

    public function testOverrideGet() {
        $methodName = 'getTest';
        $dataMock = $this->getMock('zibo\\library\\Data', array($methodName));
        $dataMock
            ->expects($this->once())
            ->method($methodName);

        $dataMock->test;
    }

    public function testIterator() {
        $data = new Data();
        $data->test = 'test';
        $data->test2 = 'test2';

        $array = array();
        foreach ($data as $key => $value) {
            $array[$key] = $value;
        }

        $this->assertEquals(array('test' => 'test', 'test2' => 'test2'), $array);
    }

}