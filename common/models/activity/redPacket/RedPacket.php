<?php
namespace common\models\activity\redPacket;

use backend\models\BaseModel;
use backend\models\MyException;
use Yii;

class RedPacket extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_activity_red_packet';
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
            [['game_type',
                'room_index',
                'is_open',
                //'play_limit_time',
                //'machine_first_reward_min_play_count',
                //'machine_first_reward_max_play_count',
                'machine_diamond_one_min',//
                'machine_diamond_one_max',//
               // 'machine_diamond_one',
                'machine_diamond_one_percent',//
                //'machine_diamond_two',
                'machine_diamond_two_min',//
                'machine_diamond_two_max',//
                'machine_diamond_two_percent',//
               // 'machine_diamond_three',
                'machine_diamond_three_min',//
                'machine_diamond_three_max',//
                'machine_diamond_three_percent',//
                'machine_diamond_min_play_count',//
                'machine_diamond_max_play_count',//
               // 'room_diamond_one',
                'room_diamond_one_min',//
                'room_diamond_one_max',//
                'room_diamond_one_play_count',//
               // 'room_diamond_two',
                'room_diamond_two_min',//
                'room_diamond_two_max',//
                'room_diamond_two_play_count',//
                'total_play_count',//
                'total_play_count_two'//
            ], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                                  => 'ID',
            'game_type'                           => '游戏类型',
            'room_index'                          => '房间',
            'is_open'                             => '开关',
            'play_limit_time'                     => '体验场体验限时（秒）',
            'machine_first_reward_min_play_count' => '体验场首次最小局数',
            'machine_first_reward_max_play_count' => '体验场首次最大局数',
            'machine_diamond_one_min'             => 'machine_diamond_one_min',
            'machine_diamond_one_max'             => 'machine_diamond_one_max',
            'machine_diamond_one'                 => '机台送钻1',
            'machine_diamond_one_percent'         => '机台送钻1概率',
            'machine_diamond_two'                 => '机台送钻2',
            'machine_diamond_two_min'             => 'machine_diamond_two_min',
            'machine_diamond_two_max'             => 'machine_diamond_two_max',
            'machine_diamond_two_percent'         => '机台送钻2概率',
            'machine_diamond_three'               => '机台送钻3',
            'machine_diamond_three_min'           => 'machine_diamond_three_min',
            'machine_diamond_three_max'           => 'machine_diamond_three_max',
            'machine_diamond_three_percent'       => '机台送钻3概率',
            'machine_diamond_min_play_count'      => '机台送钻出奖局数最小值',
            'machine_diamond_max_play_count'      => '机台送钻出奖局数最大值',
            'room_diamond_one'                    => '房间送钻1',
            'room_diamond_one_min'                => 'room_diamond_one_min',
            'room_diamond_one_max'                => 'room_diamond_one_max',
            'room_diamond_one_play_count'         => '房间送钻1局数',
            'room_diamond_two'                    => 'room_diamond_two',
            'room_diamond_two_min'                => 'room_diamond_two_min',
            'room_diamond_two_max'                => 'room_diamond_two_max',
            'room_diamond_two_play_count'         => '房间送钻2局数',
            'total_play_count'                    => '体验场累积局数18钻',
            'total_play_count_two'                => '体验场房间累积局数28钻'
        ];
    }


    public function findByGameRoom($gameType, $roomId){
        return self::find()->where('game_type = :game_type and room_index = :room_index',
            array(':game_type'=>$gameType,':room_index'=>$roomId))->asArray()->one();
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
            if( $this->validate() && $this->save() )
            {
                return $this->attributes;
            }else{
                throw new MyException( json_encode($this->getErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    public function del($id)
    {
        return self::deleteAll("id=:id",[':id'=>$id]);
    }
 
}
