<?php
namespace common\models\game;

use backend\models\ErrorCode;
use backend\models\MyException;
use Yii;

/**
 * This is the model class for table "data_dictionary_configuration_detials".
 *
 * @property string $id
 * @property string $key_type_code
 * @property string $parent_key_code
 * @property string $key_code
 * @property string $key_name
 * @property integer $value_code_int
 * @property string $value_code_decimal
 * @property string $value_code_varchar
 * @property string $value_name
 * @property string $discription
 * @property integer $order_num
 * @property integer $active
 * @property string $create_time
 * @property string $update_time
 */
class DataDictionaryConfigurationDetails extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_dictionary_configuration_details';
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
            [['key_type_code', 'key_code','value_code_varchar','key_name','active','value_code_int','value_code_decimal'], 'required','message'=>'{attribute}不能为空'],
            [['value_code_int', 'order_num', 'active', 'create_time', 'update_time'], 'integer'],
            [['value_code_decimal'], 'number'],
            [['key_type_code', 'parent_key_code', 'key_code', 'value_name'], 'string', 'max' => 50],
            [['key_name'], 'string', 'max' => 50],
            [['value_code_varchar', 'discription'], 'string', 'max' => 500]
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
                $redisKey="game:DataDictionaryConfigurationDetails*";
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID主键',
            'key_type_code' => '类型',
            'parent_key_code' => '上一级配置号',
            'key_code' => '配置号',
            'key_name' => '配置名',
            'value_code_int' => '整型配置',
            'value_code_decimal' => '小数类型',
            'value_code_varchar' => '字符类型',
            'value_name' => '配置value_name',
            'discription' => '描述',
            'order_num' => '序号',
            'active' => '是否可用',
            'create_time' => '新增时间',
            'update_time' => '更新时间',
        ];
    }

    public function tableList()
    {
        $redisKey="game:DataDictionaryConfigurationDetails:tableList";
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
