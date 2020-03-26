<?php
namespace backend\models\globalrecord;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use Yii;
use yii\db\ActiveRecord;

class GlobalRecordContent extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_global_record_content';
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
        return [
           [['type_id','account_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
//            'id' => '主键',
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
            $obj = new self;
            foreach ( $data as $key => $val )
            {
                $obj->$key = $val;
            }
            if( $obj->save() )
            {
                return $obj->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    //根据name找数据
    public function findByTypeAccount($typeId,$accountId)
    {
        return self::find()->where('type_id = :type_id and account_id = :account_id',array(':type_id'=>$typeId,':account_id'=>$accountId))->one();
    }

    /**
     * @param $typeName
     */
    public function findByTypeName($typeName)
    {
        try{
            $GlobalRecordTypeObj = $this->GlobalRecordType->findByName($typeName);
            if( empty($GlobalRecordTypeObj) ){
                throw new MyException( ErrorCode::ERROR_OBJ );
            }
            $typeId = $GlobalRecordTypeObj['id'];
            return self::find()->where('type_id = :type_id ',array(':type_id'=>$typeId))->asArray()->all();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

}
