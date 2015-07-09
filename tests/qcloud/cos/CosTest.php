<?php

use \charlestang\commonlib\qcloud\cos\Cos;

require __DIR__ . '/define.php';

/**
 * Test Cases of Cos
 *
 * @author Charles Tang <charlestang@foxmail.com>
 */
class CosTest extends PHPUnit_Framework_TestCase
{

    public function testEncode()
    {
        $method   = new ReflectionMethod('\charlestang\commonlib\qcloud\cos\Cos', 'encode');
        $method->setAccessible(true);
        $cos      = new Cos();
        $expected = [
            'YhAXn8kTrXMXtw8Hwvw/zJau4F9hPTEwMDAwMzImaz1BS0lEUW9mSTlYOXh2UU91MGM1S1Q0RHIzd0xGV0hrY1I4WVYmZT0xNDM3MTM2Nzc0JnQ9MTQzNDU0NDc3NCZyPTkxNzQ3MDc4MCZmPSZiPXRlc3RfcWNsb3VkX2FwcGlk', //多次有效签名
            'sGvpRjsnRVwIu07xIYjDR2t8/sxhPTEwMDAwMzImaz1BS0lEUW9mSTlYOXh2UU91MGM1S1Q0RHIzd0xGV0hrY1I4WVYmZT0wJnQ9MTQzNDU0NDM4OCZyPTQxOTc0OTQ0OSZmPS8xMDAwMDMyL3Rlc3RfcWNsb3VkX2FwcGlkL3Rlc3QudHh0JmI9dGVzdF9xY2xvdWRfYXBwaWQ=', //单次有效签名
        ];
        $actual   = [
            $method->invoke($cos,
                'a=1000032&k=AKIDQofI9X9xvQOu0c5KT4Dr3wLFWHkcR8YV&e=1437136774&t=1434544774&r=917470780&f=&b=test_qcloud_appid',
                'hdBMz8k3cum0k6rD8PGdRojTMrpHHorX'),
            $method->invoke($cos,
                'a=1000032&k=AKIDQofI9X9xvQOu0c5KT4Dr3wLFWHkcR8YV&e=0&t=1434544388&r=419749449&f=/1000032/test_qcloud_appid/test.txt&b=test_qcloud_appid',
                'hdBMz8k3cum0k6rD8PGdRojTMrpHHorX'),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testDirectoryExists()
    {
        $cos    = new Cos();
        $exists = $cos->directoryExists('test', 'a/');
        $this->assertContains($exists, [true, false]);
        return $exists;
    }

    /**
     * @depends testDirectoryExists
     */
    public function testCreateAndDeleteDiretory($exists)
    {
        if (!$exists) {
            $this->createDirectory();
            $this->deleteDirectory();
        } else {
            $this->deleteDirectory();
            $this->createDirectory();
        }
    }

    protected function createDirectory() {
            try {
        $cos = new Cos();
                $result = $cos->createDirectory('test', 'a/');
                $this->assertArrayHasKey('ctime', $result);
            } catch (Exception $ex) {
                $this->fail('Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
            }
    }

    protected function deleteDirectory() {
            try {
        $cos = new Cos();
                $result = $cos->deleteDirectory('test', 'a/');
                $this->assertTrue($result);
            } catch (Exception $ex) {
                $this->fail('Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
            }
    }
}
