<?php
namespace common\models;

use backend\models\BaseModel;
use backend\models\MyException;
use backend\models\redis\MyRedis;
use Yii;

/**
 * This is the model class for table "fivepk_prize_type".
 *
 * @property integer $id
 * @property integer $game_type
 * @property integer $prize_type
 * @property string $prize_name
 * @property integer $level
 * @property integer $rate
 * @property integer $status
 */
class FivepkPrizeOut extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_prize_out';
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
            [['game_type', 'sort', 'status'], 'integer'],
            [['name','operator'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'game_type' => '游戏类型',
            'name'      => '名字',
            'sort'      => '排序',
            'status'    => '状态 1否 2是',
            'operator'  => '操作人',
            'created_at'=> '创建时间',
        ];
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        return self::find()->orderBy('level ASC')->asArray()->all();
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
                $MyRedisObj = new MyRedis();
                $MyRedisObj->clear("game:FivepkPrizeOut*");
                return $this->attributes;
            }else{
                throw new MyException( json_encode($this->getErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 根据条件查询数据
     * @param $gameType
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByGameType($gameType)
    {
        $redisKey="game:FivepkPrizeOut:findByGameType:".$gameType;
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) ) {
            $obj = self::find()->where('game_type = :game_type ',array(':game_type'=>$gameType))->orderBy('sort asc')->asArray()->all();
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        }else{
            return json_decode($redisData, true);
        }
    }

    public function del($id)
    {
        $MyRedisObj = new MyRedis();
        $MyRedisObj->clear("game:FivepkPrizeOut*");
        return self::deleteAll("id=:id",[':id'=>$id]);
    }
 
}
