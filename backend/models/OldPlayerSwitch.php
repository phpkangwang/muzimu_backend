<?php

namespace backend\models;

use common\models\game\paman\FivepkPlayerPamanSetting;
use Yii;

/**
 * This is the model class for table "old_player_switch".
 *
 * @property integer $id
 * @property integer $game_type_id
 * @property integer $open_time
 * @property integer $close_time
 * @property integer $is_player_switch
 * @property integer $account_id
 */
class OldPlayerSwitch extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'old_player_switch';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['game_type_id', 'open_time', 'close_time', 'is_player_switch', 'account_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'game_type_id'     => 'Game Type ID',
            'open_time'        => 'Open Time',
            'close_time'       => 'Close Time',
            'is_player_switch' => 'Is Player Switch',
            'account_id'       => 'Account ID',
        ];
    }


    /**
     * 查询老玩家开关数据
     * @param array $conditions
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function result($conditions = [])
    {
        $query = self::find();
        //$conditions = $this->filterQueryConditions($conditions);
        if (!empty($conditions)) {
            $query->where($conditions);
        }
        $rows = $query->all();

        return $rows;
    }

    public function updateSwitch($attributes = [], $conditions = [])
    {
        $model = new OldPlayerSwitch();
        return $model->updateAll($attributes, $conditions);
    }


    /**
     * 查询老玩家开关数据
     * @param array $conditions
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function resultOne($conditions = [])
    {
        $query = self::find();
        //$conditions = $this->filterQueryConditions($conditions);
        if (!empty($conditions)) {
            $query->where($conditions);
        }
        $rows = $query->orderBy('open_time desc')->One();

        return $rows;
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->save()) {
                return $this->attributes;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  修改老玩家游戏开关
     * @param $accountId
     * @param $gameType
     * @param $switch
     */
    public function updateByAccountId($accountId, $gameType, $switch)
    {
        try {
            $switch = $switch == 1 ? 1 : 0;
            $obj    = self::find()->where('account_id = :account_id and game_type_id = :game_type_id', array(":account_id" => $accountId, ":game_type_id" => $gameType))->one();
            $this->setPamanUserOddsForEmpty($accountId, $gameType);
            if (empty($obj)) {
                $obj               = new self();
                $obj->game_type_id = $gameType;
                $obj->account_id   = $accountId;
            }
            if ($switch == 1) {
                $obj->open_time  = time();
                $obj->close_time = 0;
            } else {
                $obj->open_time  = 0;
                $obj->close_time = time();
            }
            $obj->is_player_switch = $switch;
            if ($obj->save()) {
                return $obj->attributes;
            } else {
                throw new MyException(ErrorCode::ERROR_SYSTEM . "----" . json_encode($this->getErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  修改老玩家游戏开关
     * @param $accountIds
     * @param $gameType
     * @param $switch
     */
    public function updateByAccountIds($accountIds, $gameType, $switch)
    {
        try {
            $switch = $switch == 1 ? 1 : 0;
            if ($switch == 1) {
                $open_time  = time();
                $close_time = 0;
            } else {
                $open_time  = 0;
                $close_time = time();
            }
            $updateColumn = array(
                'open_time'        => $open_time,
                'close_time'       => $close_time,
                'is_player_switch' => $switch
            );
            //如果没有数据就重新插入数据
            foreach ($accountIds as $accountId) {
                $obj = self::find()->where('account_id = :account_id and game_type_id = :game_type_id ', array(':account_id' => $accountId, ':game_type_id' => $gameType))->one();
                $this->setPamanUserOddsForEmpty($accountId, $gameType);
                if (empty($obj)) {
                    $obj               = new self();
                    $obj->account_id   = $accountId;
                    $obj->game_type_id = $gameType;
                    $obj->save();
                }
            }
            self::updateAll($updateColumn, ['and', ['in', 'account_id', $accountIds], ['game_type_id' => $gameType]]);
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 设置paman老玩家几率 如果是空才设置
     * @param $accountId
     * @param $gameType
     * @return string
     */
    private function setPamanUserOddsForEmpty($accountId, $gameType)
    {
        if ($gameType !== 10) {
            return '';
        }
        $FivepkPlayerPamanSetting = new FivepkPlayerPamanSetting();
        $obj                      = $FivepkPlayerPamanSetting->findOneByField('account_id', $accountId);
        if (empty($obj)) {
            $FivepkPlayerPamanSetting->initUserOdds($accountId);
        }
    }

    /**
     * 根据id查询多条数据
     * @param $ids array
     * @return array
     */
    public function finds($ids)
    {
        $data = array();
        if (!empty($ids)) {
            $inStr = "'" . implode("','", $ids) . "'";
            $sql   = "select * from " . self::tableName() . " where account_id in ({$inStr}) and open_time != 0";
            $data  = Yii::$app->db->createCommand($sql)->queryAll();
        }
        return $data;
    }

    /**
     *  根据用户id和游戏类型获取一条数据
     * @param $accountId
     * @param $gameType
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findByAccountGame($accountId, $gameType)
    {
        $obj = self::find()
            ->where('game_type_id = :game_type_id and account_id = :account_id',array(":game_type_id"=>$gameType, ":account_id"=>$accountId))
            ->asArray()->one();
        return $obj;
    }
}
