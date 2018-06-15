<?php
/**
 * Created by PhpStorm.
 * User: liujinxing
 * Date: 2017/5/8
 * Time: 17:42
 */

class PdoObject
{
    private static $pdo = null;

    private static $options = [
        'dns'      => '',
        'type'     => 'mysql',
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'dbname'   => '',
        'charset'  => 'utf8'
    ];

    private function __construct()
    {

    }

    /**
     * @param array $options
     *
     * @return null|PDO
     */
    public static function getInstance($options = [])
    {
        if (self::$pdo == null) {
            // 处理配置信息
            if ($options) self::$options = array_merge(self::$options, $options);

            if (empty(self::$options['dns'])) {
                switch (self::$options['type']) {
                    case 'mysql':
                        self::$options['dns'] = self::$options['type'] . ':host=' . self::$options['host'] . ';port=' . self::$options['port'] . ';dbname=' . self::$options['dbname'] . ';charset=' . self::$options['charset'];
                        break;
                    default:
                }
            }

            // 实例化对象
            self::$pdo = new \PDO(self::$options['dns'], self::$options['user'], self::$options['password']);
        }

        return self::$pdo;
    }
}