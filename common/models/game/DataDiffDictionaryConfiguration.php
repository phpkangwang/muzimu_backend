<?php
namespace common\models\game;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
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
class DataDiffDictionaryConfiguration extends BaseModel
{
    const REDIS_KEY='game:DataDiffDictionaryConfiguration';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_diff2_dictionary_configuration';
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
            [['module_code', 'key_type_code'], 'required','message'=>'{attribute}不能为空'],
            [['order_num', 'create_time', 'update_time'], 'integer'],
            [['module_code', 'key_type_code'], 'string', 'max' => 50],
            [['module_name', 'key_type_name', 'note'], 'string', 'max' => 50],
            [['discription'], 'string', 'max' => 50],
        ];
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
            'create_time' => '新增时间',
            'update_time' => '更新时间时间',
            'config_type' => '类型',
        ];
    }

    /**
     * 添加
     * @param $data
     * @return mixed
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
                $this->MyRedis->delCacheKey(self::REDIS_KEY);
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()));
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  根据$where获取数据 [field=>value,...]
     * @param $where
     * @return array
     */
    public function tableList($where)
    {

        $hashKey = 'tableList' . md5(json_encode($where));
        $data = $this->MyRedis->readCacheHash(self::REDIS_KEY, $hashKey);
        if (empty($data)) {
            $obj = self::find();
            if (!empty($where) && is_array($where)) {
                $objWhere = Tool::arrayToSql($where);
                if (!Tool::isIssetEmpty($objWhere['where'])) {
                    $obj->where($objWhere['where'], $objWhere['value']);
                }
            }
            $data = $obj->orderBy('id ASC')->asArray()->all();
            $this->MyRedis->writeCacheHash(self::REDIS_KEY, $hashKey, $data);
        }
        return $data;
    }


}
