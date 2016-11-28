<?php

class PathUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvideJoin
     */
    public function testJoin($args, $expected)
    {
        $actual = call_user_func_array('Ellis\Oxid\Console\Util\PathUtil::join', $args);
        $this->assertEquals($expected, $actual);
    }

    public function dataProvideJoin()
    {
        return array(
            array(array('hello', 'world'), 'hello/world'),
            array(array('hello/', 'world'), 'hello/world'),
            array(array('/what/', 'is', 'up'), '/what/is/up'),
        );
    }
}
