<?php

namespace common\models\game;

use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use Yii;


class RobotPlayerInfo extends \backend\models\BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'robot_player_info';
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
            'auto_id'    => 'id',
            'account_id' => '机器人',
            'location'   => '机器人所在位置(玩的游戏)[房间编号]',
            'data_json'  => '综合配置',
            'active'     => '1:占用;0:闲置',
            'note'       => '此条数据所用功能描述',
        ];
    }

    /**
     * 添加一条 或者 修改
     * @param $data
     * @return bool
     */
    public function addOne($data)
    {
        try {
            Tool::checkParam([
                'gameType', 'room', 'accountId',
//                'nextBigAwardTime', 'nextBigAward',
            ], $data);

            if (Tool::examineEmpty($data['accountId'])) {
                $obj = self::findOne(['account_id' => intval($data['accountId']), 'location' => intval($data['gameType']) . '_' . intval($data['room'])]);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }

                $dataJson = json_decode($obj->data_json, true);

                if ($dataJson['taskType'] == 1) {
                    //修改放奖
                    if (isset($data['nextBigAwardTime'])) {
                        $dataJson['nextBigAwardTime'] = (string)(strtotime($data['nextBigAwardTime']) * 1000);
                    }
                    if (isset($data['nextBigAward'])) {
                        $dataJson['nextBigAward'] = $data['nextBigAward'];
                    }
                    if (isset($data['winScore'])) {
                        $dataJson['winScore'] = $data['winScore'];
                    }
                } elseif ($dataJson['taskType'] == 2) {
                    //修改留机
                    if (isset($data['reservationBackTime'])) {
                        $dataJson['reservationBackTime'] = (string)(strtotime($data['reservationBackTime']) * 1000);
                    }
                }

                $addData = ['data_json' => json_encode($dataJson)];
                $obj->add($addData);
            } else {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            return true;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


}
