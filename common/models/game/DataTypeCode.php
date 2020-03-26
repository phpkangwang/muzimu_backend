<?php
namespace common\models\game;

use Yii;

/**
 * This is the model class for table "data_type_code".
 *
 * @property integer $id
 * @property string $type
 * @property string $type_code
 * @property string $type_name
 * @property string $create_time
 * @property string $create_user
 */
class DataTypeCode extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_type_code';
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
            [['type', 'type_code', 'type_name', 'create_time', 'operator'], 'required'],
            [['create_time'], 'integer'],
            [['type', 'type_code', 'type_name', 'operator'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型',
            'type_code' => '类型号',
            'type_name' => '类型名',
            'create_time' => '创建时间',
            'operator' => '创建人',
        ];
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
                $redisKey="game:DataTypeCode*";
                $this->MyRedis->clear( $redisKey );
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  根据type获取数据
     * @param $type
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetByType($type)
    {
        return self::find()->where('type = :type',array(':type'=>$type))->asArray()->all();
    }

    public function tableList()
    {
        $redisKey="game:DataTypeCode:tableList";
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) ) {
            $data = self::find()->orderBy('id ASC')->asArray()->all();
            $this->MyRedis->set( $redisKey, json_encode($data) );
            return $data;
        }else{
            return json_decode($redisData, true);
        }
    }
 
}
