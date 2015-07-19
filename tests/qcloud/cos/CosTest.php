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
        $this->deleteDirectoryRecursively('/');
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

    /**
     * case 5: 测试读取文件夹属性
     * @depends testCreateADirectoryWithAttribute
     */
    public function testReadDirectoryAttribute()
    {
        try {
            $result = $this->cos->statDirectory(self::UNIT_TEST_BUCKET, '/test_create_new_with_attr/');
            $this->assertArrayHasKey('biz_attr', $result);
            $this->assertEquals('attr:rwxrwxrwx|uid:123|gid:234', $result['biz_attr']);
        } catch (Exception $ex) {
            $this->fail('Failed! Case: "读取一个目录的属性"  Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
        }
    }

    /**
     * @depends testCreateADirectoryWithAttribute
     */
    public function testCreateDirectoryLevel2()
    {
        try {
            $result = $this->cos->createDirectory(self::UNIT_TEST_BUCKET, '/test_create_new_with_attr/sub_directory/');
            $this->assertArrayHasKey('ctime', $result);
            $stat = $this->cos->statDirectory(self::UNIT_TEST_BUCKET, '/test_create_new_with_attr/sub_directory/');
            $this->assertEquals('sub_directory', $stat['name']);
        } catch (Exception $ex) {
            $this->fail('Failed! Case: "创建一个二级目录，并读出属性"  Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
        }
    }

    /**
     * 测试上传一个文件
     * @depends testCreateADirectoryWithAttribute
     */
    public function testUploadFile()
    {
        $filename = __DIR__ . '/test_upload.txt';
        try {
            $result = $this->cos->uploadFile(self::UNIT_TEST_BUCKET, '/test_create_new_with_attr/test.txt', $filename);
            $this->assertArrayHasKey('access_url', $result);
            $this->assertArrayHasKey('url', $result);
            $this->assertArrayHasKey('resource_path', $result);
        } catch (Exception $ex) {
            $this->fail('Failed! Case: "上传一个文件"  Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
        }
    }

    /**
     * 测试文件是否存在的API
     * @depends testUploadFile
     */
    public function testFileExists()
    {
        $this->assertTrue($this->cos->fileExists(self::UNIT_TEST_BUCKET, '/test_create_new_with_attr/test.txt'));
    }

    /**
     * @depends testUploadFile
     */
    public function testUpdateFileAttribute()
    {
        try {
            $result = $this->cos->updateFileAttribute(self::UNIT_TEST_BUCKET, '/test_create_new_with_attr/test.txt', 'attr:rwx------|uid:123:gid:234');
            $this->assertTrue($result);
        } catch (Exception $ex) {
            $this->fail('Failed! Case: "更新一个文件的属性"  Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
        }
    }

    /**
     * @depends testUploadFile
     */
    public function testDeleteFile()
    {
        try {
            $result = $this->cos->deleteFile(self::UNIT_TEST_BUCKET, '/test_create_new_with_attr/test.txt');
            $this->assertTrue($result);
        } catch (Exception $ex) {
            $this->fail('Failed! Case: "删除一个文件"  Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
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

    protected function deleteDirectoryRecursively($dirPath)
    {
        try {
            $result = $this->cos->lsNode(self::UNIT_TEST_BUCKET, $dirPath);
            $this->assertArrayHasKey('dircount', $result);
            $this->assertArrayHasKey('filecount', $result);
            $this->assertArrayHasKey('infos', $result);
            if ($result['dircount'] > 0 || $result['filecount'] > 0) {
                foreach ($result['infos'] as $info) {
                    if (isset($info['sha'])) {
                        $deleteResult = $this->cos->deleteFile(self::UNIT_TEST_BUCKET, $dirPath . $info['name']);
                        $this->assertTrue($deleteResult);
                    } else {
                        $this->deleteDirectoryRecursively($dirPath . $info['name'] . '/');
                    }
                }
            }
            if ($dirPath != '/') {
                $deleteResult = $this->cos->deleteDirectory(self::UNIT_TEST_BUCKET, $dirPath);
                $this->assertTrue($deleteResult);
            }
        } catch (Exception $ex) {
            var_dump($ex->getTraceAsString());
            $this->fail('Code: ' . $ex->getCode() . ' Msg: ' . $ex->getMessage());
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
