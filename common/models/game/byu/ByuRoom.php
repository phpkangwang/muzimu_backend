<?php

namespace common\models\game\byu;

use backend\models\redis\MyRedis;
use backend\models\Tool;
use common\models\OddsChangePath;
use Yii;

class ByuRoom extends Byu
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room_config';
    }

    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                   => 'ID',
            'name'                 => '房间名称',
            'type'                 => '房间类型',
            'enter_battery_lv'     => '进入的最小炮台等级',
            'enter_max_battery_lv' => '进入的最大炮台等级',
            'enter_min_money'      => '进入最小金叶子',
            'init_room_count'      => '初始房间的个数',
            'delete_flag'          => '1-开0-关',
            'use_prop'             => '房间能使用的道具',
            'convert_rate'         => '钻石转换比率',
            'correction'           => '房间盈利修正值',

        ];
    }

    public function add($data)
    {
        try {
            //修改这个值必须 记录 修改的值
            $arr = Tool::distinctArr($data,$this->attributes,self::attributeLabels(), " 房间名称:".$this->name);
            if(!empty($arr)){
                $OddsChangePathModel = new OddsChangePath();
                $postData = array(
                    'game_type' => $this->gameType,
                    'type'      => $OddsChangePathModel->typeRoom,
                    'type_id'   => $this->name,
                    'content'   => json_encode($arr,JSON_UNESCAPED_UNICODE),
                );
                $OddsChangePathModel->add($postData);
            }

            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->validate() && $this->save()) {
                return $this->attributes;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取有效的房间场次 列表
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRoomList()
    {
        return self::find()->select('id as room_index,id,name,type,delete_flag,correction')->where('delete_flag = 1')->asArray()->all();
    }

    public function tableList()
    {
        $data = self::find()->select('id as auto_id,id,name,type,delete_flag,correction')->where('delete_flag = 1')->asArray()->all();
        return $data;
    }

    /**
     * 获取房间名称
     * @param $level
     * @return mixed
     */
    public function findName($level)
    {
        $data = self::find()->select('name')->where('id=:id',array(':id'=>$level))->asArray()->one();
        return $data['name'];
    }
}
