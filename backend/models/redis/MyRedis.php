<?php

namespace backend\models\redis;

use backend\models\BaseModel;
use FunctionStaticParams;
use RedisAutoClose;
use Yii;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21 0021
 * Time: 15:17
 */
class MyRedis extends BaseModel
{
    private $key;
    private $keyConfig;
    public $MyRedisConfig;

    public function __construct($config = [])
    {
        $this->MyRedisConfig = new MyRedisConfig();
        parent::__construct($config);
    }

    //设置string
    public function set($key, $value)
    {
        $this->keyInit($key);
        Yii::$app->redis->set($this->key, $value);
        Yii::$app->redis->expire($key, $this->keyConfig['time']);
        return true;
    }

    //获取string
    public function get($key)
    {
        $this->keyInit($key);
        return Yii::$app->redis->get($this->key);
    }

    //插入队列
    public function LPUSH($key, $value)
    {
        $this->keyInit($key);
        Yii::$app->redis->LPUSH($this->key, $value);
        return true;
    }

    //出队列
    public function LPOP($key)
    {
        $this->keyInit($key);
        return Yii::$app->redis->LPOP($this->key);
    }

    /**
     * 有序集合
     * @param $key     集合名称
     * @param $value   值
     * @param $score   分数
     */
    public function ZADD($key, $score, $value)
    {
        $this->keyInit($key);
        Yii::$app->redis->ZADD($this->key, $score, $value);
    }

    /**
     * 获取有序集合分数内的数据
     * @param $key     集合名称
     * @param $sScore  起始分数
     * @param $eScore  结束分数
     * @return array
     */
    public function ZRANGE($key, $sScore, $eScore)
    {
        $this->keyInit($key);
        return Yii::$app->redis->ZRANGE($this->key, $sScore, $eScore);
    }

    /**
     * 按分数获取集合中的分数范围内的数据
     * @param $key
     * @param $sScore
     * @param $eScore
     * @return mixed
     */
    public function ZRANGEBYSCORE($key, $sScore, $eScore)
    {
        $this->keyInit($key);
        return Yii::$app->redis->ZRANGEBYSCORE($this->key, $sScore, $eScore);
    }


    /**
     * 移除集合中的一个数据
     * @param $key
     * @param $value
     * @return mixed
     */
    public function ZREM($key, $value)
    {
        $this->keyInit($key);
        Yii::$app->redis->ZREM($this->key, $value);
        return true;
    }

    /**
     * 移除集合中的分数内的数据
     * @param $key
     * @param $value
     * @return mixed
     */
    public function ZREMRANGEBYSCORE($key, $sScore, $eScore)
    {
        $this->keyInit($key);
        Yii::$app->redis->ZREMRANGEBYSCORE($this->key, $sScore, $eScore);
        return true;
    }

    public function select($dataBase)
    {
        if ($dataBase == "common") {
            $dataBase = Yii::$app->params['redisCommonDatabase'];
        } elseif ($dataBase == "forever") {
            $dataBase = Yii::$app->params['redisForeverDatabase'];
        } else {
            $dataBase = Yii::$app->redis->database;
        }
        Yii::$app->redis->select($dataBase);
    }

    /**
     * 清除指定键的key，可以模糊指定 keys*
     * @param $key
     * @return bool
     */
    public function clear($key)
    {
        $this->keyInit($key);
        $redis = Yii::$app->redis;
        $keys  = $redis->keys($key);
        foreach ($keys as $k) {
            $redis->del($k);
        }
        return true;
    }

    /**
     * 重载 外部调用本类方法不存在时会直接去调redis的方法
     */
    public function __call($name, $params)
    {
        $redis = Yii::$app->redis;
        return call_user_func_array([$redis, $name], $params);
    }


    /**
     * 写入hash缓存
     * @param $cacheName string 缓存名
     * @param $key string hash key
     * @param $value mixed 数据
     * @return bool
     */
    public function writeCacheHash($cacheName, $key, $value)
    {
        $obj = $this->MyRedisConfig->getRedisConfig($cacheName);
        if ($obj != '') {
            $redis = Yii::$app->redis;
            $redis->hset($obj['key'], $key, json_encode($value));
            $redis->expire($key, $obj['time']);
        }
        return true;
    }


    /**
     * 读取hash缓存
     * @param $cacheName string 缓存名
     * @param $key string hash key
     * @return mixed
     */
    public function readCacheHash($cacheName, $key)
    {
        $obj = $this->MyRedisConfig->getRedisConfig($cacheName);
        if ($obj != '') {
            $redis = Yii::$app->redis;
            return json_decode($redis->hget($obj['key'], $key), true);
        }
        return '';
    }

    /**
     * @param $cacheName
     * @return string
     */
    public function HGTALL($cacheName)
    {
        $obj = $this->MyRedisConfig->getRedisConfig($cacheName);
        if ($obj != '') {
            $redis = Yii::$app->redis;
            return $redis->hgetall($obj['key']);
        }
        return '';
    }

    /**
     * 删除hash缓存
     * @param $cacheName string 缓存名
     * @param $key string hash key
     * @return mixed
     */
    public function delCacheHash($cacheName, $key)
    {
        $obj = $this->MyRedisConfig->getRedisConfig($cacheName);
        if ($obj != '') {
            $redis = Yii::$app->redis;
            return $redis->hdel($obj['key'], $key);
        }
        return '';
    }

    /**
     * 删除name key
     * @param $cacheName string 缓存名
     * @return mixed
     */
    public function delCacheKey($cacheName)
    {
        $obj = $this->MyRedisConfig->getRedisConfig($cacheName);
        if ($obj != '') {
            $redis = Yii::$app->redis;
            return $redis->del($obj['key']);
        }
        return '';
    }

    /**
     *  使用每个key的时候都要初始化
     * @param $key
     */
    public function keyInit($key)
    {
        $this->key       = $key;
        $this->keyConfig = $this->MyRedisConfig->getKeyConfig($key);
        $this->initDataBase();
    }

    /**
     *   初始化数据库
     */
    public function initDataBase()
    {
        $this->select($this->keyConfig['select']);
    }


    public function INCRBY($cacheName, $num)
    {
        $key   = $this->MyRedisConfig->getRedisConfig($cacheName);
        $redis = Yii::$app->redis;
        return $redis->INCRBY($key['key'], $num);
    }

    public function DECRBY($cacheName, $num)
    {
        $key   = $this->MyRedisConfig->getRedisConfig($cacheName);
        $redis = Yii::$app->redis;
        return $redis->DECRBY($key['key'], $num);
    }


}