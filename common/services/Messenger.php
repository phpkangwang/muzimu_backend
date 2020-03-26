<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/18
 * Time: 19:10
 */

namespace common\services;


use yii\base\Model;
/**
 * 传递简单消息专用
 *
 *  @property string $status 状态码
 *  @property string $message 消息体
 *  @property string $type 消息类型
 *  @property string $version 消息版本
 *  @property $data 数据对象
 */

class Messenger extends Model
{
    public $status = 10;
    public $message = '';
    public $type = 0;
    public $version = 0;
    public $data = null;

    public function fields(){
        return ['status', 'message', 'type','version','data'];
    }
}