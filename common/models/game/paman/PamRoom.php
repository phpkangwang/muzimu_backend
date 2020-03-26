<?php

namespace common\models\game\paman;

use backend\models\MyException;
use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\OddsChangePath;
use Yii;



class PamRoom extends Pam
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_room_paman';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'data_room_list_info_id' => '房间ID',
            'seo_machine_id'         => '机台名称',
            'prize_name'             => '奖型',
            'room_win_type'          => '倍率',
            'room_add_count'         => '奖项累加标准值',
            'room_buff_count'        => '房间奖BUFF',
            'today_contribution'     => '房间的日贡献度',
            'total_contribution'     => '房间的总贡献度',
            'gap'                    => '间隔局数',
            'gap_random'             => '间隔局数随机值',
            'min_limit'              => '间隔局数随机最小值',
            'max_limit'              => '间隔局数随机最大值',
            'card_type'              => '奖型类型备注',
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
            $arr = Tool::distinctArr($data,$this->attributes,self::attributeLabels(), "奖:".$this->prize_name);

            if(!empty($arr)){
                $OddsChangePathModel = new OddsChangePath();
                $postData = array(
                    'game_type' => $this->gameType,
                    'type'      => $OddsChangePathModel->typeRoom,
                    'type_id'      => $this->seo_machine_id,
                    'content'   => json_encode($arr,JSON_UNESCAPED_UNICODE),
                );
                $OddsChangePathModel->add($postData);

                foreach ($data as $key => $val) {
                    $this->$key = $val;
                }
                if ($this->validate() && $this->save()) {
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
        $data_room_list_info_id = $this->gameType . "_" . $level;
        $query = self::find()->where('data_room_list_info_id = :data_room_list_info_id', array(':data_room_list_info_id' => $data_room_list_info_id));
        if( $returnType == "obj" ){
            return $query->all();
        }else{
            return $query->asArray()->all();
        }
    }
}
