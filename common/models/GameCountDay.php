<?php
namespace common\models;

use backend\models\BaseModel;
use backend\models\MyException;
use common\models\game\FivepkAccount;
use Yii;


class GameCountDay extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'game_count_day';
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
            'id' => 'ID',
            'num' => '活跃人数',
            'create_time' => '日期',
        ];
    }

    /**
     * 添加一账户关系
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            $obj = new self();
            foreach ( $data as $key => $val )
            {
                $obj->$key = $val;
            }
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
 * 按天和游戏类型查找数据
 * @param $day
 * @return array|null|\yii\db\ActiveRecord
 */
    public function findByDay($day){
        return self::find()->where(['create_time'=>$day])->asArray()->one();
    }

    /**
     *  按天删除数据
     * @param $day
     */
    public function deleteByDay($day){
        self::deleteAll(['create_time'=>$day]);
    }
}
