<?php
namespace common\models;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\redis\MyRedis;
use Yii;


class StoreItemExchangeRecord extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'store_item_exchange_record';
    }

    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
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
            [['account_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'account_id' => '用户ID',
            'nick_name'  => '昵称',
            'phone'      => '手机号',
            'prize'      => '价格',
            'exchange_type'=> '1金币 2话费',
            'award_num'  => '奖品数量',
            'status'     => '状态',//1未发货 2已发货
            'update_time' => '修改时间',
            'create_time' => '创建时间',
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
     *  修改状态
     * @param $id
     * @return array
     * @throws MyException
     */
    public function updateStatus($id)
    {
        try{
            $obj = self::findOne($id);
            if(empty($obj)){
                throw new MyException( ErrorCode::ERROR_OBJ);
            }
            $obj->status = 2;
            $obj->update_time = time();
            if( $obj->save() )
            {
                return $obj->attributes;
            }else{
                throw new MyException( implode(",",$obj->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 分页
     * @param $pageNo
     * @param $pageSize
     * @param $where
     * @return array|\yii\db\ActiveRecord[]
     * @return array
     */
    public function page($pageNo, $pageSize, $where)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->where($where)->offset($offset)->limit($limit)->orderBy('id desc')->asArray()->all();
    }


    /**
     * 分页获取最大条数
     * @param $where
     * @return int|string
     */
    public function pageCount($where)
    {
        return self::find()->where($where)->count();
    }

    public function pageSum($pageNo, $pageSize, $where)
    {
        $rs = Tool::page($pageNo,$pageSize);
        $limit  = $rs['limit'];
        $offset = $rs['offset'];
        $sql = "
                select * from store_item_exchange_record 
                where {$where}
                group by 
        ";
    }
}
