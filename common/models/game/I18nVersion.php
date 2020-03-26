<?php

namespace common\models\game;

use backend\models\BaseModel;
use backend\models\MyException;
use Yii;

/**
 * This is the model class for table "i18n_version".
 *
 * @property int $id ID
 * @property int $id_parent 父级id
 * @property string $name 语言
 * @property string $chinese_name 中文名
 * @property string $version 版本号
 */
class I18nVersion extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'i18n_version';
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
            [['id_parent'], 'integer'],
            [['name', 'version','chinese_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_parent' => 'Id Parent',
            'name' => 'Name',
            'chinese_name' => 'Chinese Name',
            'version' => 'Version',
        ];
    }

    public function getChildren()
    {
        return $this->hasMany(self::className(),['id_parent'=>'id']);
    }

    /**
     * 修改
     * @param $data
     * @return bool
     */
    public function upd($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            if( $this->validate() && $this->save() )
            {
                $redisKey="game:I18nVersion*";
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
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            if($this->load($data) && $this->validate() && $this->save() )
            {
                $redisKey="game:I18nVersion*";
                $this->MyRedis->clear( $redisKey );
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()));
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    public function del($id)
    {
        try{
            if(self::findOne($id)->delete()){
                $redisKey="game:I18nVersion*";
                $this->MyRedis->clear( $redisKey );
                return $this->attributes;
            }else{
                throw new MyException('删除失败!');
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }
}
