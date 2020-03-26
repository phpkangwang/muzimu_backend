<?php

namespace common\models\game;

use backend\models\BaseModel;
use backend\models\MyException;
use Yii;

/**
 * This is the model class for table "data_server_lists".
 *
 * @property int $id ID
 * @property string $server_name 服务器名称
 * @property string $server_ip 服务器IP
 * @property string $server_port 服务器端口
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property string $created_by 创建人
 * @property string $updated_by 更新人
 */
class DataServerLists extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_server_lists';
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
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['server_name', 'server_ip', 'server_port', 'created_by', 'updated_by'], 'string', 'max' => 255],
            [['id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'server_name' => 'Server Name',
            'server_ip' => 'Server Ip',
            'server_port' => 'Server Port',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function upd($data)
    {
        try{
            if($this->load($data) && $this->validate() && $this->save() )
            {
                $redisKey="game:DataServerLists*";
                $this->MyRedis->clear( $redisKey );
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()));
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    public function add($data)
    {
        try{
            if($this->load($data) && $this->validate() && $this->save() )
            {
                $redisKey="game:DataServerLists*";
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
                $redisKey="game:DataServerLists*";
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
