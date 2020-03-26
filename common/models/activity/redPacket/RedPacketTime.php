<?php
namespace common\models\activity\redPacket;

use backend\models\BaseModel;
use backend\models\MyException;
use Yii;

class RedPacketTime extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_activity_red_packet_time';
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
            [['activity_id','start_time','end_time'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
        ];
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        return self::find()->asArray()->all();
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            $this->load(['RedPacketTime'=>$data]);
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

    public function findByActivityId($activityId){
        return self::find()->where('activity_id = :activity_id',
            array( ':activity_id'=>$activityId ))->asArray()->all();
    }

    public function setIsCrontab()
    {
        self::updateAll(['is_crontab' => 1]);
    }
}
