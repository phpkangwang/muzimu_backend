<?php
namespace common\models\game;

use Yii;

/**
 * This is the model class for table "data_fivepk_newer_contribution_rate".
 *
 * @property integer $id
 * @property integer $pre_newer_contribution_rate
 * @property integer $suf_newer_contribution_rate
 * @property integer $pre_newer_gap
 * @property integer $suf_newer_gap
 * @property string $last_time
 */
class DataFivepkNewerContributionRate extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_fivepk_newer_contribution_rate';
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
            [['id', 'pre_newer_contribution_rate', 'suf_newer_contribution_rate', 'pre_newer_gap', 'suf_newer_gap'], 'integer'],
            [['pre_newer_contribution_rate', 'suf_newer_contribution_rate', 'pre_newer_gap', 'suf_newer_gap'],'required'],
            [['last_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'pre_newer_contribution_rate' => '门槛下限',
            'suf_newer_contribution_rate' => '门槛上限',
            'pre_newer_gap'=>'局数上限',
            'suf_newer_gap'=>'局数下限',
            'last_time' => 'Last Time',
        ];
    }

    public function tableList()
    {
        return self::find()->orderBy('id desc')->asArray()->all();
    }

 
}
