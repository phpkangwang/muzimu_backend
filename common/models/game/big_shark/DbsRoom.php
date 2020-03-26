<?php
namespace common\models\game\big_shark;

use backend\models\MyException;
use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\OddsChangePath;
use Yii;


class DbsRoom extends Dbs
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_room_bigshark';
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
//            [['prefab_five_bars', 'prefab_royal_flush', 'prefab_five_of_a_kind', 'prefab_five_of_a_kind_times', 'prefab_straight_flush', 'prefab_four_of_a_kind_J_A', 'prefab_four_of_a_kind_J_A_times', 'gap', 'gap_random', 'min_bye', 'max_bye'], 'integer','min'=>0],
//            [['prefab_five_bars_count', 'prefab_royal_flush_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_J_A_count'], 'number','min'=>0],
//            [['data_room_list_info_id', 'seo_machine_id'], 'string', 'max' => 255],
//            [['prefab_four_of_a_kind_J_A_times_record'], 'string', 'max' => 2555],
//            [['gap', 'gap_random', 'min_bye', 'max_bye','prefab_five_bars_count', 'prefab_royal_flush_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_J_A_count'],'required'],
//            [['min_bye', 'max_bye'],'validateMinMax'],
        ];
    }

    public function validateMinMax($attribute)
    {
        try{
            if($this->max_bye <= $this->min_bye) {
                //$this->addError($attribute, '随机最小值必须小于随机最大值');
                throw new MyException( "随机最小值必须小于随机最大值");
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID主键',
            'data_room_list_info_id' => 'Data Room List Info ID',
            'seo_machine_id' => '房间类型',
            'prefab_five_bars' => '五鬼',
            'prefab_five_bars_count' => '五鬼BUFFER',
            'prefab_royal_flush' => '同花大顺',
            'prefab_royal_flush_count' => '同花大顺BUFFER',
            'prefab_five_of_a_kind' => '五梅',
            'prefab_five_of_a_kind_count' => '五梅BUFFER',
            'prefab_five_of_a_kind_times' => '五梅倍数',
            'prefab_straight_flush' => '同花小顺',
            'prefab_straight_flush_count' => '同花小顺BUFFER',
            'prefab_four_of_a_kind_J_A' => '正宗大四梅',
            'prefab_four_of_a_kind_J_A_count' => '正宗大四梅BUFFER',
            'prefab_four_of_a_kind_J_A_times' => '正宗大四梅倍数',
            'prefab_four_of_a_kind_J_A_times_record' => 'Prefab Four Of A Kind  J  A Times Record',
            'gap' => '间隔',
            'gap_random' => '间隔随机值',
            'min_bye' => '随机最小值',
            'max_bye' => '随机最大值',
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
     * @desc 关联房间
     * @return \yii\db\ActiveQuery
     */
    public function getRoomLevel()
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
