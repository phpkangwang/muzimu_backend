<?php

namespace common\models\game;

use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use Yii;


class RobotPlayerSettingData extends \backend\models\BaseModel
{

    const STATUS_GHR_OPEN = 2;//赛马特殊要求开启要为2

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'robot_player_setting_data';
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
            'robot_type' => '机器人参与游戏或者活动类型',
            'start_time' => '开始时间每日的HH:ss',
            'end_time'   => '结束时间每日的HH:ss',
            'data_json'  => '功能配置',
            'note'       => '此条数据所用功能描述',
            'active'     => '1:有效;0:无效',

            'robot_player_setting_data' => '下次任务执行所需时间范围',
            'task_json'                 => '下次任务执行任务配置'
        ];
    }

    /**
     * 添加一条 或者 修改
     * @param $data
     * @return bool
     */
    public function addOne($data)
    {
        Tool::checkParam([
            'gameType', 'room',
            'start_time', 'end_time'
//            , 'robotCount', 'betGroupRange', 'betScoreRange'
        ], $data);
        if ($data['gameType'] == 12) {
            //赛马
            Tool::checkParam([
                'robotCount', 'betGroupRange', 'betScoreRange'
            ], $data);
            return $this->addGhrOne($data);
        } elseif ($data['gameType'] == 11) {
            //雪豹
            Tool::checkParam([
                'next_task_execute_time_range', 'task_json',
                'mechineEmptyRate', 'reservationRate', 'changeRobotRate'
            ], $data);
            return $this->addBaoOne($data);
        } elseif ($data['gameType'] == 1) {
            //火凤凰
            Tool::checkParam([
                'next_task_execute_time_range', 'task_json', 'bet_range',
                'mechineEmptyRate', 'reservationRate', 'changeRobotRate'
            ], $data);
            return $this->addOtherOne($data);
        } elseif ($data['gameType'] == 2) {
            //大白鲨
            Tool::checkParam([
                'next_task_execute_time_range', 'task_json', 'bet_range',
                'mechineEmptyRate', 'reservationRate', 'changeRobotRate'
            ], $data);
            return $this->addOtherOne($data);
        } elseif ($data['gameType'] == 3) {
            //大字版
            Tool::checkParam([
                'next_task_execute_time_range', 'task_json', 'bet_range',
                'mechineEmptyRate', 'reservationRate', 'changeRobotRate'
            ], $data);
            return $this->addOtherOne($data);
        } elseif ($data['gameType'] == 8) {
            //超级大亨
            Tool::checkParam([
                'next_task_execute_time_range', 'task_json', 'bet_range',
                'mechineEmptyRate', 'reservationRate', 'changeRobotRate'
            ], $data);
            return $this->addOtherOne($data);
        }

        return false;
    }

    //赛马添加 如果游戏多了要迁移出去
    public function addGhrOne(&$data)
    {
        try {

            self::verifyData($data);

            $addData = [
                'robot_type' => $data['gameType'] . '_' . $data['room'],
                'start_time' => $data['start_time'],
                'end_time'   => $data['end_time'],
                'data_json'  => $this->getDataJson($data),
            ];

            if (!Tool::verifyJsonNum($data['robotCount'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (!Tool::verifyJsonNum($data['betGroupRange'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (!Tool::verifyJsonNum($data['betScoreRange'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (isset($data['bet_range']) && is_array($data['bet_range'])) {
                foreach ($data['bet_range'] as $key => $value) {
                    $value = intval($value);
                    if (empty($value)) {
                        unset($data['bet_range'][$key]);
                    }
                }
                if (empty($data['bet_range'])) {
                    throw new MyException(ErrorCode::ERROR_HORSE_RATE_RANGE_PARAM);
                }
                $addData['bet_range'] = Tool::json_encode($data['bet_range']);
            } else {
                throw new MyException(ErrorCode::ERROR_HORSE_RATE_RANGE_PARAM);
            }

            if (
                $data['betScoreRange'][0] < 5
                || $data['betScoreRange'][0] % 5 != 0
                || $data['betScoreRange'][1] > 995
                || $data['betScoreRange'][1] % 5 != 0
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (Tool::examineEmpty($data['auto_id'])) {
                $obj = self::findOne(['auto_id' => intval($data['auto_id'])]);
                if (empty($obj)) {
                    return false;
                }
                $obj->add($addData);
            } else {
                $addData['note']   = '{robotCount:[机器人数量区间],betGroupRange:[机器人下注门数区间],betScoreRange:[机器人总押注分区间]}';
                $addData['active'] = self::STATUS_GHR_OPEN;
                $obj               = self::add($addData);
            }

            return $obj;

        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //火凤凰|大字版|大白鲨|火凤凰添加
    public function addOtherOne(&$data)
    {
        try {
            self::verifyData($data);

            if (!Tool::verifyJsonNum($data['mechineEmptyRate'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (!Tool::verifyJsonNum($data['reservationRate'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (!Tool::verifyJsonNum($data['changeRobotRate'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $addData = [
                'robot_type'                   => $data['gameType'] . '_' . $data['room'],
                'start_time'                   => $data['start_time'],
                'end_time'                     => $data['end_time'],
                'next_task_execute_time_range' => Tool::json_encode($data['next_task_execute_time_range']),
                'task_json'                    => Tool::json_encode($data['task_json']),
                'bet_range'                    => '[' . implode(',', array_map('intval', explode(',', $data['bet_range']))) . ']',
                'data_json'                    => $this->getBaoDataJson($data),
                'win_award_range'              => ''
            ];

            if (Tool::examineEmpty($data['auto_id'])) {
                $obj = self::findOne(['auto_id' => intval($data['auto_id'])]);
                if (empty($obj)) {
                    return false;
                }
                $obj->add($addData);
            } else {
                $addData['note']   = '{mechineEmptyRate:[该房间机台闲置占比],reservationRate:[机台留机数量占比,每次校验出留机的概率],changeRobotRate:[机台换座率]}';
                $addData['active'] = 1;
                $obj               = self::add($addData);
            }

            return $obj;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //雪豹添加 如果游戏多了要迁移出去
    public function addBaoOne(&$data)
    {
        try {
            self::verifyData($data);

            if (!Tool::verifyJsonNum($data['mechineEmptyRate'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (!Tool::verifyJsonNum($data['reservationRate'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (!Tool::verifyJsonNum($data['changeRobotRate'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            /*
             * 中文名称	对应奖型	对应倍率
            大火车	        302	80
            小BAR (25倍)	401	25
            中BAR (50倍)	402	50
            大BAR (100倍)	403	100
            大满贯	        501	200
             */

            $config = [
                302 => 80,
                401 => 25,
                402 => 50,
                403 => 100,
                501 => 200,
            ];
            //{"401":"[20,2000]","402":"[50,2000]","403":"[200,2000]"}
            $win_award_range = [];
            foreach ($data['task_json'] as $key => $value) {
                $win_award_range[$key] = Tool::json_encode([0 * $config[$key], 95 * $config[$key]]);
            }

            $addData = [
                'robot_type'                   => $data['gameType'] . '_' . $data['room'],
                'start_time'                   => $data['start_time'],
                'end_time'                     => $data['end_time'],
                'next_task_execute_time_range' => Tool::json_encode($data['next_task_execute_time_range']),
                'task_json'                    => Tool::json_encode($data['task_json']),
                'data_json'                    => $this->getBaoDataJson($data),
                'win_award_range'              => json_encode($win_award_range)
            ];

            if (Tool::examineEmpty($data['auto_id'])) {
                $obj = self::findOne(['auto_id' => intval($data['auto_id'])]);
                if (empty($obj)) {
                    return false;
                }
                $obj->add($addData);
            } else {
                $addData['note']   = '{mechineEmptyRate:[该房间机台闲置占比],reservationRate:[机台留机数量占比,每次校验出留机的概率],changeRobotRate:[机台换座率]}';
                $addData['active'] = 1;
                $obj               = self::add($addData);
            }

            return $obj;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //验证数据
    public function verifyData(&$data)
    {
        try {
            if (!Tool::verifyMS($data['start_time'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            if (!Tool::verifyMS($data['end_time'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $sTime = explode(':', $data['start_time']);
            $eTime = explode(':', $data['end_time']);
            if (
                $sTime[0] > $eTime[0]
                ||
                (
                    $sTime[0] == $eTime[0]
                    && $sTime[1] > $eTime[1]
                )
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    //赛马添加 如果游戏多了要迁移出去
    public function getDataJson(&$data)
    {
        return json_encode([
            'robotCount'    => Tool::json_encode($data['robotCount']),
            'betGroupRange' => Tool::json_encode($data['betGroupRange']),
            'betScoreRange' => Tool::json_encode($data['betScoreRange']),
        ]);
    }

    //雪豹添加 如果游戏多了要迁移出去
    public function getBaoDataJson(&$data)
    {
        return json_encode([
            'mechineEmptyRate' => Tool::json_encode($data['mechineEmptyRate']),
            'reservationRate'  => Tool::json_encode($data['reservationRate']),
            'changeRobotRate'  => Tool::json_encode($data['changeRobotRate']),
        ]);
    }

    /**
     * 删除一条
     * @param $data
     * @return bool
     */
    public function deleteOne(&$data)
    {
        Tool::checkParam(['auto_id'], $data);
        return self::deleteAll(['auto_id' => intval($data['auto_id'])]);
    }

    /**
     * 修改状态
     * @param $data
     * @return bool
     */
    public function updateActive(&$data)
    {
        Tool::checkParam(['auto_id', 'active'], $data);
        if (!in_array($data['active'], [0, 1])) {
            return false;
        }

        $obj = self::findOne(['auto_id' => $data['auto_id']]);
        if (empty($obj)) {
            return false;
        }

        //赛马特殊要求 开启要变为2
        if (
            strpos($obj->robot_type, '12_') !== false
            && $data['active'] == 1
        ) {
            $data['active'] = self::STATUS_GHR_OPEN;
        }

        return $obj->add(['active' => intval($data['active'])]);
    }


}
