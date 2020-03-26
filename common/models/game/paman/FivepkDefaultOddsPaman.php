<?php
namespace common\models\game\sbb;

use common\models\DataRoomInfoList;
use backend\models\BaseModel;
use Yii;


class FivepkDefaultOddsPaman extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'paman_default';
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
            [['create_date'], 'safe'],
            [['room_info_list_id'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_info_list_id' => '房间配置id',
            'create_date' => '创建时间',
        ];
    }

    /**
     * 添加修改
     * @param $data
     * @return array
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
                throw new MyException(json_encode($this->getErrors()));
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        $data = self::find()->asArray()->all();
        return $data;
    }

    /**
     * 关联房间信息
     * @return \yii\db\ActiveQuery
     */
    public function getRoomList()
    {
        $result = DataRoomInfoList::find()->filterWhere(['id'=>$this->room_info_list_id])->one();
        return $result;
    }

    /**
     * 查找基本数据
     * @param $id
     * @return DataGameListInfo|mixed|null
     */
    public function findBase($id)
    {
        $obj = self::find()->where(['id' => $id])->asArray()->one();
        return $obj;
    }

 
}
