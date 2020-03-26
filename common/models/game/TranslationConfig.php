<?php
namespace common\models\game;

use Yii;
use backend\models\MyException;
use backend\models\ErrorCode;
use backend\models\redis\MyRedis;

/**
 * This is the model class for table "translation_config    ".
 *
 * @property integer $id
 * @property integer $title
 * @property integer $translation
 * @property string $updated_at
 * @property string $created_at
 * @property string $type
 * @property string $status
 * @property string $operator
 */
class TranslationConfig extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'translation_config';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'updated_at', 'created_at', 'operator'], 'required'],
            [['title', 'translation', 'operator'], 'string', 'max' => 255],
            [['title'], 'unique'],
            [['updated_at', 'created_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '名字',
            'translation' => '翻译',
            'updated_at' => '修改时间',
            'created_at' => '创建时间',
            'operator' => '操作人',

        ];
    }

    /**
     * 添加修改
     * @param $data
     * @return array
     */
    public function add($data)
    {
        try {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->validate() && $this->save()) {
                $MyRedisObj = $this->MyRedis;
                $MyRedisObj->clear("game:TranslationConfig*");
                return $this->attributes;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 查找基本数据
     * @param $id
     * @return DataGameListInfo|mixed|null
     */
    public function findBase($id)
    {
        $redisKey = "game:TranslationConfig:" . $id;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $obj = self::find()->where(['id' => $id])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     *  列表
     * @return array
     */
    public function tableList()
    {
        $redisKey = "game:TranslationConfig:tableList";
        $redisData = $this->MyRedis->get($redisKey);
        $redisData='';
        if (empty($redisData)) {
            $data = self::find()->orderBy('id DESC')->asArray()->all();
            //由于是引用注意val
            foreach ($data as &$val){
                $val['created_at']=date('Y-m-d H:i:s',$val['created_at']);;
                $val['updated_at']=date('Y-m-d H:i:s',$val['updated_at']);;
            }
            $this->MyRedis->set($redisKey, json_encode($data));
            return $data;
        } else {
            return json_decode($redisData, true);
        }
    }

    public function del($id)
    {
        $this->MyRedis->clear("game:TranslationConfig*");
        return self::deleteAll("id=:id",[':id'=>$id]);
    }

}
