<?php
namespace common\models\game;

use backend\models\Tool;
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
class DataDiffTypeCode extends \backend\models\BaseModel
{
    const REDIS_KEY = 'game:DataDiffDictionaryConfiguration';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_diff2_type_code';
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
            [['type', 'type_code', 'type_name', 'operator', 'update_time'], 'required'],
            [['create_time','update_time'], 'integer'],
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
            'discription' => '描述',
            'update_time' => '更新时间',
        ];
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->validate() && $this->save()) {
                $this->clearAllCache();
                return $this->attributes;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //由于这里是公共模块要清除全部缓存
    public function clearAllCache(){
        $this->MyRedis->delCacheKey(self::REDIS_KEY);
        $this->MyRedis->delCacheKey(\common\models\game\DataDiffDictionaryConfiguration::REDIS_KEY);
        $this->MyRedis->delCacheKey(\common\models\game\DataDiffDictionaryConfigurationDetails::REDIS_KEY);
    }

    /**
     *  根据type获取数据
     * @param $type
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetByType($type)
    {
        return self::find()->where('type = :type', array(':type' => $type))->asArray()->all();
    }

    /**
     *  根据$where获取数据 [field=>value,...]
     * @param $where
     * @param $option
     * @return array
     */
    public function tableList($where,$option=[])
    {

        $hashKey = 'tableList' . md5(json_encode(array_merge($where, $option)));
        $data = $this->MyRedis->readCacheHash(self::REDIS_KEY, $hashKey);
        if (empty($data)) {
            $obj = self::find();
            if (!empty($where) && is_array($where)) {
                $objWhere = Tool::arrayToSql($where,$option);
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
