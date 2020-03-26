<?php
namespace backend\models\globalrecord;

use backend\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

class GlobalRecordType extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_global_record_type';
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
           [['name', 'content', 'status', 'operator'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
//            'id' => '主键',
        ];
    }

    /**
     * 添加一个后台账号的功能权限
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            $obj = new self;
            foreach ( $data as $key => $val )
            {
                $obj->$key = $val;
            }
            if( $obj->save() )
            {
                return $obj->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 分页获取所有的后台功能权限
     * @return array
     */
    public function page($pageNo, $pageSize)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->orderBy('id desc')->offset($offset)->limit($limit)->asArray()->all();
    }

    /**
     *  获取最大条数
     */
    public function accountNum()
    {
        return self::find()->count();
    }

    /**
     * 删除一个后台功能权限
     * @param $id
     * @return int 删除的个数
     */
    public function del($id)
    {
        return self::deleteAll("id=:id",[':id'=>$id]);
    }

    //根据name找数据
    public function findByName($name)
    {
        return self::find()->where('name = :name',array(':name'=>$name))->asArray()->one();
    }


}
