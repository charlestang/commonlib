<?php

use \charlestang\commonlib\qcloud\cos\Cos;
use \charlestang\commonlib\qcloud\cos\Error;

if (file_exists(__DIR__ . '/define.php')) {
    require __DIR__ . '/define.php';
} else {
    die("Please create a file define.php to set your APP_ID, SECRET_ID, SECRET_KEY.");
}

/**
 * Test Cases of Cos
 *
 * @author Charles Tang <charlestang@foxmail.com>
 */
class CosTest extends PHPUnit_Framework_TestCase
{

    /**
     * 用于执行测试用例的 bucket 名
     */
    const UNIT_TEST_BUCKET = 'unit_test';

    //以上常量定义

    protected $cos;

    protected function setUp()
    {
        $this->cos = new Cos();
    }

    /**
     * 测试整理用例运行的条件是否存在
     */
    public function testRequirements()
    {
        $this->assertTrue($this->cos->directoryExists(self::UNIT_TEST_BUCKET, '/'), '请创建一个名为 unit_test 的 bucket，用于执行测试用例');
        return '/test_exists/';
    }

    /**
     * @depends testRequirements
     * @return boolean
     */
    public function testDirectoryExists($dirPath)
    {
        $exists = $this->cos->directoryExists(self::UNIT_TEST_BUCKET, $dirPath);
        $this->assertContains($exists, [true, false]);
        return $exists;
    }

    /**
     * 测试用例数据清理，删除该测试用例中建立的所有目录和文件，确保下一次可以正确执行所有用例
     * @depends testRequirements
     */
    public function testClearData()
    {
        $emptyDirectories = [
            '/test_create_new/',
            '/test_create_new_with_attr/',
        ];
        foreach ($emptyDirectories as $dir) {
            if ($this->testDirectoryExists($dir)) {
                $this->deleteDirectory($dir);
            }
        }
    }

    /**
     * case 1: 创建一个全新的不存在的目录
     * @depends testClearData
     */
    public function testCreateAWholeNewDirectory()
    {
        try {
            $result = $this->cos->createDirectory(self::UNIT_TEST_BUCKET, '/test_create_new/');
            $this->assertArrayHasKey('ctime', $result);
        } catch (Exception $ex) {
            $this->fail('Failed! Case: "创建一个全新的空目录" Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
        }
    }

    /**
     * case2: 创建一个同名的目录
     * @depends testCreateAWholeNewDirectory
     */
    public function testCreateAlreadyExistsDirectory()
    {
        $this->setExpectedExceptionRegExp('\charlestang\commonlib\qcloud\cos\Exception', '/.*/', Error::ERR_PATH_CONFLICT);
        $this->cos->createDirectory(self::UNIT_TEST_BUCKET, '/test_create_new/');
    }

    /**
     * case 3: 测试创建一个已经存在的同名目录
     * @depends testCreateAWholeNewDirectory
     */
    public function testOverwriteADirectory()
    {
        try {
            $result = $this->cos->createDirectory(self::UNIT_TEST_BUCKET, 'test_create_new/', '', Cos::OVERWRITE);
            $this->assertArrayHasKey('ctime', $result);
        } catch (Exception $ex) {
            $this->fail('Failed! Case: "覆盖一个同名的空目录" Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
        }
    }

    /**
     * case 4: 创建一个带有属性的不存在的目录
     * @depends testClearData
     */
    public function testCreateADirectoryWithAttribute()
    {

        try {
            $result = $this->cos->createDirectory(self::UNIT_TEST_BUCKET, '/test_create_new_with_attr/', 'attr:rwxrwxrwx|uid:123|gid:234');
            $this->assertArrayHasKey('ctime', $result);
        } catch (Exception $ex) {
            $this->fail('Failed! Case: "创建一个带有属性的新目录"  Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
        }
        
    }

    protected function deleteDirectory($dirPath)
    {
        try {
            $this->assertTrue($this->cos->deleteDirectory(self::UNIT_TEST_BUCKET, $dirPath));
        } catch (Exception $ex) {
            $this->fail('Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
        }
    }

    public function testUploadFile()
    {
        $cos      = new Cos();
        $filename = __DIR__ . '/test_upload.txt';
        try {
//$result = $cos->uploadFile('test', 'test.txt', $filename);
//var_dump($result);
            $res2 = $cos->deleteFile('test', 'test.txt');
            var_dump($res2);
        } catch (Exception $ex) {
            var_dump($ex->getCode(), $ex->getMessage());
        }
    }

//<editor-fold defaultstate="collapsed" desc="测试签名编码算法">
    /**
     * 测试腾讯云的签名编码算法，现在算法使用的数据，是文档的范例数据，所使用的范例数据日期是2015-07-11日，如果日后有任何变化，
     * 可以参考该用例中的快照对比。
     */
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
            $method->invoke($cos, 'a=1000032&k=AKIDQofI9X9xvQOu0c5KT4Dr3wLFWHkcR8YV&e=1437136774&t=1434544774&r=917470780&f=&b=test_qcloud_appid', 'hdBMz8k3cum0k6rD8PGdRojTMrpHHorX'),
            $method->invoke($cos, 'a=1000032&k=AKIDQofI9X9xvQOu0c5KT4Dr3wLFWHkcR8YV&e=0&t=1434544388&r=419749449&f=/1000032/test_qcloud_appid/test.txt&b=test_qcloud_appid', 'hdBMz8k3cum0k6rD8PGdRojTMrpHHorX'),
        ];

        $this->assertEquals($expected, $actual);
    }

//</editor-fold>
}
