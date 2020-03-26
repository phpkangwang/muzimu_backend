<?php

namespace common\models\game\star97;

use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\OddsChangePath;
use Yii;


class RoomRewardPoolStar97 extends Mxj
{
    public $rewardId;
    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try {
            //修改这个值必须 记录 修改的值
            $arr = Tool::distinctArr($data, $this->attributes, $this->attributeLabels(), " 房间奖池".$this->rewardId );
            //获取这个场次的名称
            $DataRoomInfoListModel = new DataRoomInfoList();
            $DataRoomInfoListObj   = $DataRoomInfoListModel->findOne($this->room_info_list_id);

            if (!empty($arr)) {
                $OddsChangePathModel = new OddsChangePath();
                $postData            = array(
                    'game_type' => $this->gameType,
                    'type'      => $OddsChangePathModel->typeRoom,
                    'type_id'   => $DataRoomInfoListObj->seo_machine_id,
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
     * 获取某个房间的配置
     * @param $level
     * @param string $returnType
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findByLevel($level, $returnType = "obj")
    {
        $room_info_list_id = $this->gameType . "_" . $level;
        $query = self::find()->where('room_info_list_id = :room_info_list_id', array(':room_info_list_id' => $room_info_list_id));
        if( $returnType == "obj" ){
            return $query->one();
        }else{
            return $query->asArray()->one();
        }
    }

}
