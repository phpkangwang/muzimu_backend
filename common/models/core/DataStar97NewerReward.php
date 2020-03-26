<?php
namespace common\models\core;

use Yii;

/**
 * This is the model class for table "data_star97_newer_reward".
 *
 * @property integer $id
 * @property string $reward_name
 * @property integer $is_open
 * @property integer $total_buff_count
 * @property integer $today_contribution_percent
 * @property integer $total_contribution_percent
 * @property integer $min_interval_count
 * @property integer $max_interval_count
 * @property integer $newer_star97_play_count
 */
class DataStar97NewerReward extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_star97_newer_reward';
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
            [['is_open', 'total_buff_count', 'today_contribution_percent', 'total_contribution_percent', 'min_interval_count', 'max_interval_count', 'newer_star97_play_count'], 'integer'],
            [['reward_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reward_name' => '奖项名称',
            'is_open' => '0-关，1-开',
            'total_buff_count' => '触顶值',
            'today_contribution_percent' => '今日贡献度占比/100',
            'total_contribution_percent' => '总贡献度占比/100',
            'min_interval_count' => '最小间隔局数',
            'max_interval_count' => '最大间隔局数',
            'newer_star97_play_count' => '明星97新人局数',
        ];
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

    /**
     * 查找基本数据
     * @param $id
     * @return DataRoomInfoList|mixed|null
     */
    public function findBase($id)
    {
        return self::find()->where(['id' => $id])->asArray()->one();
    }

    /**
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function tableList()
    {
        return self::find()->asArray()->all();
    }
 
}
