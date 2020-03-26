<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-28
 * Time: 11:25
 */

namespace backend\models\forms;


use common\services\ToolService;
use yii\base\Model;

class FastTestForm extends Model
{
    public $loopTimes;// 循环次数
    public $playCounts;// 每轮局数
    public $machineCount;// 测试机台数量
    public $sleepSeconds;// 每轮间隔时间
    public $port;// 游戏服socket端口号
    public $betScore;// 押注分
    public $gameType;// 游戏类型

    public function rules()
    {
        return [
            [['loopTimes','port','betScore','playCounts','machineCount','gameType','sleepSeconds'],'required'],
            [['loopTimes','port','betScore','playCounts','machineCount','gameType','sleepSeconds'],'integer'],
            [['loopTimes','port','betScore','playCounts','machineCount','gameType'],'compare','operator' => '>','compareValue' => 0,'message' => '{attribute}必须大于0'],
        ]; // TODO: Change the autogenerated stub
    }

    public function attributeLabels()
    {
        return [
            'loopTimes'=>'循环次数',
            'playCounts'=>'每轮局数',
            'machineCount'=>'测试机台数量',
            'sleepSeconds'=>'每轮间隔时间',
            'port'=>'游戏服socket端口号',
            'betScore'=>'押注分',
            'gameType'=>'游戏类型',
        ]; // TODO: Change the autogenerated stub
    }

    public function update()
    {
        $arr = $this->toArray();

        $url = \Yii::$app->params['url']."/runFastTest";

        $contents = ToolService::send_post($url,$arr);

        return $contents;
    }
}