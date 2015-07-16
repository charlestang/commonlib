<?php

/**
 * Description of newPHPClass
 *
 * @author charles
 */
class LogDbTest extends PHPUnit_Framework_TestCase
{

    public function testSend()
    {
        $conf = [
            'id'        => 'int',
            'key_name'  => 'varchar',
            'key_value' => 'text',
            'update'    => 'datetime',
        ];

        $logdb = charlestang\commonlib\logdb\LogDb::getInstance('172.16.57.128', '22060', $conf);
        $ret   = $logdb->send([
            'key_name'  => 'this is a test to key insert',
            'key_value' => 'this the body value of test content',
        ]);

        $this->assertTrue($ret);
    }

}
