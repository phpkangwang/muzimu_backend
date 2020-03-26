<?php
namespace common\models\game;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use Yii;

/**
 * This is the model class for table "data_dictionary_configuration".
 *
 * @property string $id
 * @property string $module_code
 * @property string $module_name
 * @property string $key_type_code
 * @property string $key_type_name
 * @property integer $order_num
 * @property string $discription
 * @property string $note
 * @property integer $active
 * @property string $create_time
 * @property string $update_time
 */
class DataDictionaryConfiguration extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_dictionary_configuration';
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
            [['module_code', 'key_type_code','active'], 'required','message'=>'{attribute}不能为空'],
            [['order_num', 'active', 'create_time', 'update_time'], 'integer'],
            [['module_code', 'key_type_code'], 'string', 'max' => 50],
            [['module_name', 'key_type_name', 'note'], 'string', 'max' => 50],
            [['discription'], 'string', 'max' => 50],
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
                $redisKey="game:DataDictionaryConfiguration*";
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID主键',
            'module_code' => '模块号',
            'module_name' => '模块名',
            'key_type_code' => '类型号',
            'key_type_name' => '类型名',
            'order_num' => '序号',
            'discription' => '描述',
            'note' => '备注',
            'active' => '是否可用',
            'create_time' => '新增时间',
            'update_time' => '更新时间时间',
        ];
    }

    public function tableList()
    {
        $redisKey="game:DataDictionaryConfiguration:tableList";
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) ) {
            $data = self::find()->orderBy('order_num ASC')->asArray()->all();
            $this->MyRedis->set( $redisKey, json_encode($data) );
            return $data;
        }else{
            return json_decode($redisData, true);
        }
    }
}
