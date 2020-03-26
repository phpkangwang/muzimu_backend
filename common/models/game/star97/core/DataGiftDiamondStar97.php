<?php
namespace common\models\game\star97\core;

use Yii;

/**
 * This is the model class for table "data_gift_diamond_star97".
 *
 * @property integer $id
 * @property string $room_info_list_id
 * @property integer $five_seven
 * @property integer $six_seven
 * @property integer $seven_seven
 * @property integer $eight_seven
 * @property integer $all_seven
 * @property integer $all_fruits
 * @property integer $mixed_bars
 * @property integer $all_cherry
 * @property integer $all_red
 * @property integer $all_yellow
 * @property integer $all_blue
 * @property integer $all_orange
 * @property integer $all_mango
 * @property integer $all_watermelon
 * @property integer $all_bell
 * @property integer $is_open
 */
class DataGiftDiamondStar97 extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_gift_diamond_star97';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('core_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['five_seven', 'six_seven', 'seven_seven', 'eight_seven', 'all_seven', 'all_fruits', 'mixed_bars', 'all_cherry', 'all_red', 'all_yellow', 'all_blue', 'all_orange', 'all_mango', 'all_watermelon', 'all_bell', 'is_open'], 'integer'],
            [['room_info_list_id'], 'string', 'max' => 25]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_info_list_id' => 'Room Info List ID',
            'five_seven' => 'Five Seven',
            'six_seven' => '六个七',
            'seven_seven' => '七个7',
            'eight_seven' => '8个7',
            'all_seven' => '全盘7',
            'all_fruits' => '全盘水果',
            'mixed_bars' => '杂牌龍',
            'all_cherry' => 'All Cherry',
            'all_red' => 'All Red',
            'all_yellow' => 'All Yellow',
            'all_blue' => 'All Blue',
            'all_orange' => 'All Orange',
            'all_mango' => 'All Mango',
            'all_watermelon' => 'All Watermelon',
            'all_bell' => 'All Bell',
            'is_open' => '开关',
        ];
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            if( $this->save() )
            {
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 根据房间id获取数据
     * @return array
     */
    public function findByRoomId($roomId)
    {
        return self::find()->where("room_info_list_id=:room_info_list_id",[':room_info_list_id'=>$roomId])->asArray()->one();
    }
}
