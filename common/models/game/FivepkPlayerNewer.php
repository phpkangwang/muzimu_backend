<?php
namespace common\models\game;

use Yii;

/**
 * This is the model class for table "fivepk_player_newer".
 *
 * @property string $id
 * @property string $account_id
 * @property integer $game_type
 * @property integer $data_fivepk_newer_win_type_id
 * @property integer $count
 * @property integer $play_count
 */
class FivepkPlayerNewer extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_newer';
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
            [['account_id', 'game_type', 'data_fivepk_newer_win_type_id', 'count', 'play_count'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => '玩家id',
            'game_type' => '游戏类型1-火凤凰',
            'data_fivepk_newer_win_type_id' => '奖型0-为查看当前游戏局数的行',
            'count' => '已中奖个数',
            'play_count' => '当前游戏局数',
        ];
    }

    public function getWinType()
    {
        return $this->hasOne(DataFivepkNewerWinType::className(),['win_type'=>'win_type']);
    }

    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(),['account_id'=>'account_id']);
    }

    /**
     * 根据游戏类型和用户id获取数据
     * @param $gameType
     * @param $AccountId
     */
    public function findByGameTypeAccoountId($AccountId, $gameType)
    {
        return self::find()->where('account_id = :accountId and game_type = :gameType',array(':accountId'=>$AccountId,':gameType'=>$gameType))->asArray()->all();
    }

    public function getNewer($accountIds)
    {
        return self::find()->filterWhere(['in','account_id',$accountIds])->andFilterWhere(['data_fivepk_newer_win_type_id'=>0,'win_type'=>0])->orderBy('game_type ASC')->asArray()->all();
    }

    /**
     *   获取所有的新人
     */
    public function getNewerList()
    {
        return self::find()->where(['data_fivepk_newer_win_type_id'=>0,'win_type'=>0])->asArray()->all();
    }

    /**
     * 根据id查询多条数据
     * @param $ids array
     * @return array
     */
    public function finds($ids)
    {
        return self::find()->where(['in','account_id',$ids])->asArray()->all();
    }

}
