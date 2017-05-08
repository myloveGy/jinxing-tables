<?php
/**
 * Created by PhpStorm.
 * User: liujinxing
 * Date: 2017/5/8
 * Time: 17:42
 */

class Pdo
{
    private static $pdo = null;

    private static $options = [
        'dns' => '',
        'type' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => '',
        'dbname' => '',
        'charset' => 'utf8'
    ];

    private function __construct()
    {

    }

    /**
     * getInstance() 获取redis信息
     * @param array $options
     * @return null|\Redis
     */
    public static function getInstance($options = [])
    {
        if (self::$pdo == null) {
            // 处理配置信息
            if ($options) self::$options = array_merge(self::$options, $options);

            if (empty(self::$options['dns'])) {
                switch (self::$options['type']) {
                    case 'mysql':
                        self::$options['dns'] = self::$options['type'].':host='.self::$options['host'].';port='.self::$options['port'].';dbname='.self::$options['dbname'];
                        break;
                    default:
                }
            }

            var_dump(self::$options);

            // 实例化对象
            self::$pdo = new \PDO(self::$options['dns'], self::$options['user'], self::$options['password']);
            if (self::$options['type'] == 'mysql') {
                self::$pdo->exec('SET NAMES '.self::$options['charset']);
            }
        }

        // return self::$pdo;
    }
}