<?php

namespace common\models\game\sbb;

use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\OddsChangePath;
use Yii;


class SbbRoom extends Sbb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_room_super_big_boss';
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
        return [
//            [['room_win_type', 'gap', 'gap_random', 'min_bye', 'max_bye'], 'integer'],
//            [['room_add_count', 'room_buff_count'], 'number'],
//            [['data_room_list_info_id', 'seo_machine_id', 'prize_name', 'card_type'], 'string', 'max' => 255],
//            [['room_add_count', 'room_buff_count', 'gap', 'gap_random', 'min_bye', 'max_bye'],'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID主键',
            'data_room_list_info_id' => '房间ID',
            'seo_machine_id'         => '房间类型',
            'room_win_type'          => '奖型',
            'room_add_count'         => '累积值',
            'room_buff_count'        => 'buff值',
            'gap'                    => '间隔',
            'gap_random'             => '间隔随机',
            'min_bye'                => '随机最小值',
            'max_bye'                => '随机最大值',
            'prize_name'             => '奖名',
            'card_type'              => '牌型',
        ];
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try {
            //修改这个值必须 记录 修改的值
            $arr = Tool::distinctArr($data, $this->attributes, self::attributeLabels(), "奖:".$this->prize_name);

            if (!empty($arr)) {
                $OddsChangePathModel = new OddsChangePath();
                $postData            = array(
                    'game_type' => $this->gameType  ,
                    'type'      => $OddsChangePathModel->typeRoom,
                    'type_id'   => $this->seo_machine_id,
                    'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
                );
                $OddsChangePathModel->add($postData);

                foreach ($data as $key => $val) {
                    $this->$key = $val;
                }
                if ($this->save()) {
                    return $this->attributes;
                } else {
                    throw new MyException(implode(",", $this->getFirstErrors()));
                }
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 关联房间
     * @return \yii\db\ActiveQuery
     */
    public function getRoomLevel()
    {
        return $this->hasOne(DataRoomInfoList::className(), ['id' => 'data_room_list_info_id']);
    }


    /**
     * 获取某个房间的配置
     * @param $level
     * @param string $returnType
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findByLevel($level, $returnType = "obj")
    {
        $Tool = new Tool();
        $data_room_list_info_id = $this->gameType . "_" . $level;
        $query = self::find()->where('data_room_list_info_id = :data_room_list_info_id', array(':data_room_list_info_id' => $data_room_list_info_id))->orderBy('card_type DESC,room_win_type DESC');
        if( $returnType == "obj" ){
            $data = $query->all();
        }else{
            $data =  $query->asArray()->all();
        }
        foreach ($data as $key=>$val){
            $data[$key] = $Tool->clearFloatZero($val);
        }
        return $data;
    }
}
