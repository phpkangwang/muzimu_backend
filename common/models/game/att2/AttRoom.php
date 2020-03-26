<?php

namespace common\models\game\att2;

use backend\models\Tool;
use common\models\OddsChangePath;
use Yii;

class AttRoom extends Att
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_room_att2';
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
//            [['royal_flush_add_count', 'royal_flush_total_count', 'five_of_a_kind_add_count', 'five_of_a_kind_total_count', 'straight_flush_add_count', 'straight_flush_total_count', 'four_of_a_kind_T_T_add_count', 'four_of_a_kind_T_T_total_count'], 'integer'],
//            [['royal_flush_current_count', 'five_of_a_kind_current_count', 'straight_flush_current_count', 'four_of_a_kind_T_T_current_count'], 'number'],
//            [['room_list_info_id', 'seo_machine_id'], 'string', 'max' => 255],
//            [['royal_flush_add_count','royal_flush_total_count','royal_flush_current_count',
//                'five_of_a_kind_add_count','five_of_a_kind_total_count','five_of_a_kind_current_count',
//                'straight_flush_add_count','straight_flush_total_count','straight_flush_current_count',
//                'four_of_a_kind_T_T_add_count','four_of_a_kind_T_T_total_count','four_of_a_kind_T_T_current_count',
//            ], 'required'],
//            [['royal_flush_total_count','five_of_a_kind_total_count','straight_flush_total_count','four_of_a_kind_T_T_total_count'], 'compare','compareValue'=>0,'operator'=>'>'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_list_info_id' => 'Room List Info ID',
            'seo_machine_id' => '房间类型',
            'royal_flush_add_count' => '大顺每局累积值',
            'royal_flush_total_count' => '大顺触顶值',
            'royal_flush_current_count' => '大顺当前BUFF值',
            'five_of_a_kind_add_count' => '五梅每局累积值',
            'five_of_a_kind_total_count' => '五梅触顶值',
            'five_of_a_kind_current_count' => '五梅当前BUFF值',
            'straight_flush_add_count' => '小顺每局累积值',
            'straight_flush_total_count' => '小顺触顶值',
            'straight_flush_current_count' => '小顺当前BUFF值',
            'four_of_a_kind_T_T_add_count' => '四梅每局累积值',
            'four_of_a_kind_T_T_total_count' => '四梅触顶值',
            'four_of_a_kind_T_T_current_count' => '四梅当前BUFF值',
            'four_of_a_kind_T_T_switch'=>'四梅开关',
            'straight_flush_switch'=>'小顺开关',
            'royal_flush_switch'=>'大顺开关',
            'five_of_a_kind_switch'=>'五梅开关',
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
            $arr = Tool::distinctArr($data, $this->attributes, self::attributeLabels());

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
     * 过滤查询字段
     * @return array 处理后的条件数组
     */
    public function filterQueryConditions($conditions)
    {
        $returnarr = [];
        $fields = $this->attributeLabels();
        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                if (isset($fields[$key])  &&  '' !== $value) {
                    $returnarr[$key] = trim($value);
                }
            }
        }
        return $returnarr;
    }

    public function getResultOne($conditions = []){
        $query = self::find();
        $conditions = $this->filterQueryConditions($conditions);
        if (!empty($conditions)) {
            $query->where($conditions);
        }
        $result = $query->one();

        return $result;
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
        $query = self::find()->where('room_list_info_id = :data_room_list_info_id', array(':data_room_list_info_id' => $data_room_list_info_id));
        if( $returnType == "obj" ){
            return $query->one();
        }else{
            return $query->asArray()->one();
        }
    }
}
