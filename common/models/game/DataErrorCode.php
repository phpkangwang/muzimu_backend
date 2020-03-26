<?php
namespace common\models\game;

use backend\models\BaseModel;
use Yii;

/**
 * This is the model class for table "data_error_code".
 *
 * @property integer $error_code
 * @property string $comment
 * @property integer $return
 * @property string $update_time
 * @property integer $play_voice
 */
class DataErrorCode extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_error_code';
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
            [['error_code', 'play_voice'], 'required'],
            [['error_code', 'return', 'play_voice'], 'integer'],
            [['update_time'], 'safe'],
            [['comment'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'error_code' => '错误码',
            'comment' => '备注',
            'return' => '是否return 0-返回1-不返回',
            'update_time' => 'Update Time',
            'play_voice' => '是否播放错误声音0-不播放;1-播放',
        ];
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function  add($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            if( $this->validate() && $this->save() )
            {
                $redisKey="game:DataErrorCode*";
                $this->MyRedis->clear( $redisKey );
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()));
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @param int $code
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getMessage($code = 0){
        if(!empty($code)){
            $result = self::find()->where(['error_code'=>$code])->asArray()->one();
            if(!empty($result)){
                return $result['comment'];
            }else{
                return '未记录该错误码：'.$code;
            }
        }else{
            return '通讯连接失败';
        }

    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        $redisKey="game:DataErrorCode:tableList";
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) ) {
            $data = self::find()->orderBy('error_code ASC')->asArray()->all();
            $this->MyRedis->set( $redisKey, json_encode($data) );
            return $data;
        }else{
            return json_decode($redisData, true);
        }
    }

}
