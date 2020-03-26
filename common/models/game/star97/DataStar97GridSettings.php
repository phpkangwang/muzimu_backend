<?php
namespace common\models\game\star97;

use Yii;

/**
 * This is the model class for table "data_star97_grid_settings".
 *
 * @property integer $id
 * @property integer $prefab_id
 * @property string $grid_one
 * @property string $grid_two
 * @property string $grid_three
 * @property string $grid_four
 * @property string $grid_five
 * @property string $grid_six
 * @property string $grid_seven
 * @property string $grid_eight
 * @property string $grid_nine
 */
class DataStar97GridSettings extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_star97_grid_settings';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('core_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prefab_id'], 'integer'],
            [['grid_one', 'grid_two', 'grid_three', 'grid_four', 'grid_five', 'grid_six', 'grid_seven', 'grid_eight', 'grid_nine'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键id',
            'prefab_id' => '档位',
            'grid_one' => '格子1',
            'grid_two' => '格子2',
            'grid_three' => '格子3',
            'grid_four' => '格子4',
            'grid_five' => '格子5',
            'grid_six' => '格子6',
            'grid_seven' => '格子7',
            'grid_eight' => '格子8',
            'grid_nine' => '格子9',
        ];
    }

    /**
     * 获取所有档位
     * @return array
     */
   public function getPrefabIds()
   {
       return self::find()->select('prefab_id')->asArray()->all();
   }

    /**
     * 根据PrefabId获取详情
     * @param $prefabId
     * @return array
     */

    public function findByPrefabId($prefabId)
    {
        return self::find()->where('prefab_id=:prefab_id',array(":prefab_id"=>$prefabId))->asArray()->one();
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
 
}
