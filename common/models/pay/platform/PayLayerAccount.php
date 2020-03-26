<?php
namespace common\models\pay\platform;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use Yii;

class PayLayerAccount extends BaseModel
{
    /**
     *   没有用户都要有的默认层级id
     */
    const D_LAYER = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_layer_account';
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
            'admin_account_id' => '后台用户id',
            'name'   => '代号',
            'sort'   => '排序',
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
        return self::find()
            ->where('admin_account_id = :admin_account_id or id = :D_LAYER',
                array(
                    ':admin_account_id'=>$accountId ,
                    ':D_LAYER'=>self::D_LAYER ,
                )
            )
            ->orderBy('sort asc')->asArray()->all();
    }

    /**
     * 根据用户ID获得这个用户层级数量
     * @param $idsArr
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getCountOfAccount($idsArr){

        return self::find()->select('count(*) as count,admin_account_id')->where(['in', 'admin_account_id', $idsArr])->indexBy('admin_account_id')->asArray()->groupBy('admin_account_id')->all();
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
            //id为 D_LAYER 的时候这个是不允许修改的
            if($this->id == PayLayerAccount::D_LAYER){
                throw new MyException(ErrorCode::ERROR_PARAM);
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
        if($id != self::D_LAYER){
            return self::deleteAll("id=:id",[':id'=>$id]);
        }
    }
}
