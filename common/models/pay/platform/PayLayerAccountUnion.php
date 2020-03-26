<?php
namespace common\models\pay\platform;

use backend\models\BaseModel;
use backend\models\Tool;
use Yii;

class PayLayerAccountUnion extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_layer_account_union';
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'=>'ID',
            'pay_accept_account_id' => '账户id',
            'pay_layer_account_id' => '层级id',
            'name' => '层级名称',
        ];
    }


    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        return self::find()->orderBy('admin_account_id asc,sort asc')->asArray()->all();
    }

    public function findByAccount($accountId)
    {
        return self::find()->where('admin_account_id = :admin_account_id',array(':admin_account_id'=>$accountId))->orderBy('sort asc')->asArray()->all();
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
                return $this->attributes;
            }else{
                throw new MyException( json_encode($this->getErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    public function del($id)
    {
        return self::deleteAll("id=:id",[':id'=>$id]);
    }
}
