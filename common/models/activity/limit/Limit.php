<?php
namespace common\models\activity\limit;

use backend\models\BaseModel;
use backend\models\MyException;
use Yii;

class Limit extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_activity_limit';
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
            [['game_type','room_index','is_open','extar_score','left_count','limit_count','activity_times'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'game_type' => '游戏类型',
            'room_index' => '房间',
            'is_open' => '开关',
            'extar_score' => '额外送分数',
            'left_count' => '剩余个数',
            'limit_count' => '活动个数',
            'activity_times' => '活动倍数'
        ];
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        return self::find()->orderBy('level ASC')->asArray()->all();
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

    public function findByGameRoom($gameType, $roomId){
        return self::find()->where('game_type = :game_type and room_index = :room_index',
            array(':game_type'=>$gameType,':room_index'=>$roomId))->asArray()->one();
    }

    public function del($id)
    {
        return self::deleteAll("id=:id",[':id'=>$id]);
    }
 
}
