<?php
namespace common\models\game;

use Yii;

/**
 * This is the model class for table "data_fivepk_newer_win_type".
 *
 * @property integer $id
 * @property integer $win_type
 * @property integer $win_type_rate
 * @property integer $limit_count
 * @property string $last_time
 * @property integer $game_type
 * @property string $comment
 */
class DataFivepkNewerWinType extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_fivepk_newer_win_type';
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
            [['id', 'win_type', 'win_type_rate', 'limit_count','game_type'], 'integer'],
            [['last_time'], 'safe'],
            [['comment'],'string'],
            [['win_type','win_type_rate','limit_count','game_type','comment'],'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'win_type' => '奖型',
            'win_type_rate' => '倍率',
            'limit_count' => '次数上限',
            'last_time' => 'Last Time',
            'game_type' => '游戏类型',
            'comment' => '备注',
        ];
    }


    public function GetNewerWinTypeCount($accout_id)
    {
        return FivepkPlayerNewer::find()->select('sum(count) as count')->andFilterWhere(['win_type'=>$this->win_type])->andFilterWhere(['account_id'=>$accout_id])->groupBy('win_type')->asArray()->one();
    }

    /**
     *   获取所有的win_type类型
     */
    public function getWinTypeList($gameType)
    {
        return self::find()->where('game_type = :game_type',array(':game_type'=>$gameType))->groupBy('win_type')->asArray()->all();
    }

    public function tableList($gameType)
    {
        return self::find()->where('game_type = :game_type',array(':game_type'=>$gameType))->orderBy('id desc')->asArray()->all();
    }



 
}
