<?php
namespace backend\models;

use Yii;
use backend\models\ErrorCode;
use backend\models\MyException;

/**
 * This is the model class for table "fivepk_seoid_diamond".
 *
 * @property integer $id
 * @property string $seoid
 * @property integer $diamond
 */
class FivepkSeoidDiamond extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_seoid_diamond';
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
            [['diamond'], 'integer'],
            [['seoid'], 'string', 'max' => 100],
            [['seoid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'seoid' => 'Seoid',
            'diamond' => 'Diamond',
        ];
    }

    /**
     * 添加修改
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
            if( $this->save() )
            {
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    public static function deleteSeoId($seoids =array()){
        $result = FivepkSeoidDiamond::deleteAll(['seoid'=>$seoids]);
        return $result;
    }

    public function findByPopCode($popCode)
    {
        return self::find()->where('seoid = :seoid',array(':seoid'=>$popCode))->one();
    }


 
}
