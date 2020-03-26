<?php

namespace common\models\game\byu;

use backend\models\Tool;
use common\models\OddsChangePath;
use Yii;

class ByuRoomFish extends Byu
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room_fish_config';
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
            'id'                 => 'ID',
            'room_config_id'     => '房间ID',
            'fish_config_id'     => '鱼的ID',
            'refresh_start_time' => '刷新开始时间',
            'refresh_end_time'   => '刷新结束时间',
            'group_min_count'    => '每组数量的最小鱼数量',
            'group_max_count'    => '每组数量最大鱼个数',
            'space_time'         => '每个鱼出现的间隔时间',
            'drop_id'            => '额外掉落Id',
            'score'              => '鱼倍率',
            'cur_gap'            => '当前间隔局数',
            'win_gap'            => '命中间隔局数',
            'min_random_gap'     => '随机间隔局数最小值',
            'max_random_gap'     => '随机间隔局数最大值',
            'max_rate'           => '转盘最大倍率',
            'min_rate'           => '转盘最小倍率',
            'show'               => '0-不展示  1-展示',
            'board_message'      => '0-不发公告 1-发公告',
            'delete_flag'        => '0-不展示  1-展示',
            'extra_config'       => '额外配置',
            'backend_show_level' => '后台显示级别',
            'backend_show'       => '后台是否显示这条鱼 1是 2否',
        ];
    }

    public function add($data)
    {
        try {
            //修改这个值必须 记录 修改的值
            $arr = Tool::distinctArr($data, $this->attributes, self::attributeLabels()," 修改的鱼: ".$this->getModelFish()->findNameById($this->fish_config_id));
            if (!empty($arr)) {
                $OddsChangePathModel = new OddsChangePath();
                $postData            = array(
                    'game_type' => $this->gameType,
                    'type'      => $OddsChangePathModel->typeRoom,
                    'type_id'   => $this->getModelRoom()->findName($this->room_config_id),
                    'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
                );
                $OddsChangePathModel->add($postData);
            }

            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->validate() && $this->save()) {
                return $this->attributes;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 根据房间获取所有鱼的配置
     * @param $roomId
     * @return array
     * @throws \yii\db\Exception
     */
    public function findByRoom($roomId)
    {
        $sql = "
            select 
              fish.id as fish__id,
              fish.desc as fish__desc,
              fish.score as fish__score,
              fish.score_max as fish__score_max,
              roomFish.backend_show as roomFish__backend_show,
              roomFish.backend_show_level as roomFish__backend_show_level,
              roomFish.score as roomFish__score,
              roomFish.show as roomFish__show,
              roomFish.board_message as roomFish__board_message,
              roomFish.extra_config as roomFish__extra_config
            from {$this->tableRoomFish} as roomFish
            left join {$this->tableFish} as fish on fish.id = roomFish.fish_config_id
            where  roomFish.room_config_id = {$roomId} and roomFish.backend_show = 1
            order by fish.id asc
        ";
        return self::getDb()->createCommand($sql)->queryAll();
    }

    /**
     * 根据 level  和  fishId 获取数据
     * @param $level
     * @param $fishId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findByRoomFish($level, $fishId)
    {
        return self::find()->where('room_config_id = :room_config_id and fish_config_id = :fish_config_id',
            array(':room_config_id' => $level, ':fish_config_id' => $fishId))
            ->one();
    }
}
