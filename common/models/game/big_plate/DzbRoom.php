<?php
namespace common\models\game\big_plate;

use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\OddsChangePath;
use Yii;


class DzbRoom extends Dzb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_room_bigplate';
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
//            [['prefab_royal_flush_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_T_T_count'], 'number'],
//            [['prefab_royal_flush', 'prefab_straight_flush', 'prefab_five_of_a_kind', 'gap', 'gap_random', 'min_bye', 'max_bye', 'prefab_four_of_a_kind_T_T'], 'integer'],
//            [['data_room_list_info_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'auto_id' => 'ID主键',
            'data_room_list_info_id' => 'Data Room List Info ID',
            'prefab_royal_flush_count' => '同花大顺BUFFER',
            'prefab_five_of_a_kind_count' => '五梅BUFFER',
            'prefab_straight_flush_count' => '小顺BUFFER',
            'prefab_royal_flush' => '大顺累积值',
            'prefab_straight_flush' => '小顺累积值',
            'prefab_five_of_a_kind' => '五梅累积值',
            'gap' => '间隔',
            'gap_random' => '间隔随机值',
            'min_bye' => '随机最小值',
            'max_bye' => '随机最大值',
            'prefab_four_of_a_kind_T_T_count' => '四梅BUFFER',
            'prefab_four_of_a_kind_T_T' => '四梅累积值',
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
                    'game_type' => $this->gameType,
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


    public function getRoomList()
    {
        return $this->hasOne(DataRoomInfoList::className(),['id'=>'data_room_list_info_id']);
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
            return $query->one();
        }else{
            return $query->asArray()->one();
        }
    }
}
