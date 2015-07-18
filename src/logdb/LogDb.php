<?php

namespace charlestang\commonlib\logdb;

class LogDb
{

    const COMMAND_TYPE_DATA = 0;
    const COMMAND_TYPE_SQL  = 1;

    protected $ip;
    protected $port;
    protected $conf             = [];
    protected static $instances = [];

    protected function __construct($ip, $port, $conf)
    {
        $this->ip   = $ip;
        $this->port = $port;
        $this->conf = $conf;
    }

    protected static function key($ip, $port)
    {
        return $ip . ':' . $port;
    }

    public static function getInstance($ip, $port, $conf = [])
    {
        $instance = null;
        $key      = self::key($ip, $port);
        if (isset(self::$instances[$key])) {
            $instance = self::$instances[$key];
        } else {
            $instance              = new self($ip, $port, $conf);
            self::$instances[$key] = $instance;
        }
        return $instance;
    }

    /**
     * 发送LogDB数据
     * @param array|string $data 数据|sql语句，请注意：
     * 1）如果是array，表示数据，具体值请看实现类的定义，例如：array('corpuin' => '2355199800', 'cost_time' => 3.1415, 'account' => 'wintiongan', )
     * 2）如果是string，表示sql语句，不支持记录的返回，一般仅限于insert语句
     * @return boolean
     */
    public function send($data)
    {
        if (empty($data)) {
            return;
        }

        /* >>>>>>包头开始 */
        $bin = '';
        $bin .= pack('C', 0); // 结果，只在返回包中有效，非0 代表失败 uint8_t result;
        $bin .= pack('C', is_array($data) ? self::COMMAND_TYPE_DATA : self::COMMAND_TYPE_SQL); // uint8_t command; 0: 表示包体中为数据，LogDB 解析数据执行入库操作 1: 表示包体为 SQL 语句，LogDB 执行该SQL 语句
        $bin .= pack('N', rand(1, 65535)); // uint32_t sequence; 序列号，返回包会回带此字段
        $bin .= pack('n', 0); // uint16_t echo_len; 回带字段长度
        //$bin .= pack('C', 0); // uint8_t echo_buf[100]; 回带字段, 最多100个字节，如果echo_len为0，所以不用传这个字段
        /* <<<<<<包头结束 */

        /* >>>>>>包体开始 */
        if (is_array($data)) {
            foreach ($this->conf as $field_name => $field_type) {
                $value = isset($data[$field_name]) ? $data[$field_name] : '';
                switch ($field_type) {
                    case 'varchar':
                    case 'text':
                    case 'blob':
                    case 'binary':
                        $bin .= pack('n', strlen($value)); // 2个字节表示长度
                        $bin .= pack('a*', $value);
                        break;

                    case 'char':
                    case 'tinytext':
                    case 'tinyblob':
                    case 'varbinary':
                    case 'date':
                    case 'time':
                    case 'datetime':
                        $bin .= pack('C', strlen($value)); // 1个字节表示长度
                        $bin .= pack('a*', $value);
                        break;

                    case 'tinyint':
                        $bin .= pack('C', $value);
                        break;

                    case 'smallint':
                        $bin .= pack('n', $value);
                        break;

                    case 'int':
                        $bin .= pack('N', sprintf('%d', (float) $value)); // 为了兼容21亿
                        break;

                    case 'bigint':
                        $gmp_v         = gmp_init($value);
                        $gmp_64bit_max = gmp_init("18446744073709551616");
                        $gmp_32bit_max = gmp_init("4294967296");

                        if (gmp_sign($gmp_v) == -1) {
                            $gmp_v = gmp_add($gmp_64bit_max, $gmp_v);
                        }
                        $gmp_h = gmp_div($gmp_v, $gmp_32bit_max);
                        $gmp_l = gmp_mod($gmp_v, $gmp_32bit_max);

                        $bin .= pack('N*N*', sprintf('%d', (float) gmp_strval($gmp_h)),
                            sprintf('%d', (float) gmp_strval($gmp_l)));
                        break;

                    case 'ubigint':
                        $gmp_v         = gmp_init($value);
                        $gmp_32bit_max = gmp_init("4294967296");

                        $gmp_h = gmp_div($gmp_v, $gmp_32bit_max); // 高位
                        $gmp_l = gmp_mod($gmp_v, $gmp_32bit_max); // 低位

                        $bin .= pack('N*N*', sprintf('%d', (float) gmp_strval($gmp_h)),
                            sprintf('%d', (float) gmp_strval($gmp_l)));
                        break;

                    case 'float':
                        $bin .= strrev(pack('f', $value));
                        break;

                    case 'double':
                        $bin .= strrev(pack('d', $value));
                        break;

                    default:
                        // 不支持的类型
                        break;
                }
            }
        } else {
            $bin .= pack('a*', $data . '0');
        }
        /* <<<<<<包体结束 */

        // 发包
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_nonblock($sock);
        socket_sendto($sock, $bin, strlen($bin), MSG_DONTROUTE, $this->ip, $this->port);
        socket_close($sock);
        return true; // 不等待回包，直接返回
    }

}
