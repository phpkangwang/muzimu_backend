<?php

namespace common\models;

use backend\models\redis\MyRedis;
use Yii;

/**
 * This is the model class for table "data_room_info_list".
 *
 * @property string $id
 * @property integer $game
 * @property integer $room_index
 * @property string $name
 * @property integer $bet_score
 * @property string $has_reword
 * @property integer $check_in
 * @property integer $reward_score
 * @property integer $clearance_score
 * @property integer $bonus_score
 * @property integer $machine_score
 * @property string $room_bg
 * @property string $room_clicked_sound
 * @property string $seo_machine_id
 */
class DataRoomInfoList extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_room_info_list';
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
//            [['id'], 'required'],
//            [['game', 'room_index', 'bet_score', 'check_in', 'reward_score', 'clearance_score', 'bonus_score', 'machine_score'], 'integer'],
//            [['id', 'name', 'has_reword', 'room_bg', 'room_clicked_sound', 'seo_machine_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => '万单位为游戏类型1-火凤凰 个单位为场次1-新手2-初3-中4-高',
            'game'               => '游戏类型1-火凤凰',
            'room_index'         => '场次顺序',
            'name'               => '房间名',
            'bet_score'          => '押注分',
            'has_reword'         => '是否有奖券0-没有1-有',
            'check_in'           => '带入',
            'reward_score'       => '过关奖每玩一局彩金增加的数值',
            'clearance_score'    => '过关彩金',
            'bonus_score'        => '爆机彩金',
            'machine_score'      => '比倍爆机上限',
            'room_bg'            => '场次图片',
            'room_clicked_sound' => '场次按钮声音',
            'seo_machine_id'     => '房间编号',
        ];
    }

    static $DataRoomInfoListFindBase;

    /**
     * 查找基本数据
     * @param $id
     * @return DataRoomInfoList|mixed|null
     */
    public function findBase($id)
    {
        $redisKey = "game:DataRoomInfoList:" . $id;
        if (isset(self::$DataRoomInfoListFindBase[$redisKey])) {
            return self::$DataRoomInfoListFindBase[$redisKey];
        }
        $MyRedis   = new MyRedis();
        $redisData = $MyRedis->get($redisKey);
        if (empty($redisData)) {
            $redisData = self::find()->where(['id' => $id])->asArray()->one();
            $MyRedis->set($redisKey, json_encode($redisData));
        } else {
            $redisData = json_decode($redisData, true);
        }

        self::$DataRoomInfoListFindBase[$redisKey] = $redisData;
        return $redisData;
    }

    /**
     *  获取列表
     * @param $gameType 游戏类型
     * @return array
     */
    public function tableList($gameType)
    {
        $redisKey="game:DataRoomInfoList:tableList:".$gameType;
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) ) {
            $where = " 1";
            if( !empty($gameType) ){
                $where .= " and game = '$gameType'";
            }
            $data = self::find()->where($where)->asArray()->all();
            $this->MyRedis->set( $redisKey, json_encode($data) );
            return $data;
        }else{
            return json_decode($redisData, true);
        }
    }

    /**
     * 通过游戏类型获取列表
     * @param $game 游戏类型
     * @return array
     */
    public function findByGame($game)
    {
        $redisKey  = "game:DataRoomInfoList:findByGame" . $game;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $data = self::find()->where(['game' => $game])->asArray()->all();
            $this->MyRedis->set($redisKey, json_encode($data));
            return $data;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     * 通过游戏类型获取列表  id=>name
     * @param $gameType 游戏类型
     * @return array
     */
    public function findByGameMachineIdName($gameType)
    {
        $machineLevel        = array();
        $DataRoomInfoListObj = $this->findByGame($gameType);
        foreach ($DataRoomInfoListObj as $val) {
            $machineLevel[$val['id']] = $val['name'];
        }
        return $machineLevel;
    }

    /**
     * 通过游戏类型和room_index获取一条数据
     * @param $game 游戏类型
     * @param $index   room_index
     * @return array
     */
    public function findByGameIndex($game, $index)
    {
        $redisKey  = "game:DataRoomInfoList:findByGameIndex" . $game . ":" . $index;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $data = self::find()->where(['game' => $game, 'room_index' => $index])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($data));
            return $data;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     * 通过 机台号 获取列表
     * @param $seoMachineId 机台号
     * @return array
     */
    public function findBySeoMachineId($seoMachineId)
    {
        return self::find()->filterWhere(['like', 'seo_machine_id', $seoMachineId])->orderBy('room_index ASC')->asArray()->one();
    }


}
