<?php
namespace common\models;

use backend\models\ErrorCode;
use backend\models\MyException;
use Yii;

/**
 * This is the model class for table "player_is_change".
 *
 * @property integer $id
 * @property integer $game_type
 * @property integer $account_id
 * @property string $column
 */
class OldPlayerJpChangePaman extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'old_player_jp_change_paman';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['column'],'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => '玩家ID',
            'column' => '修改过的字段',
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
                throw new MyException( implode(",",$this->getFirstErrors()));
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 根据用户id和游戏类型获取数据
     * @param $accountId  用户id
     * @param $gameType   游戏类型
     * @return array
     */
    public function findByAccountGame($accountId, $gameType)
    {
        return self::find()->where("account_id=:account_id and game_type=:game_type",[':account_id'=>$accountId, ':game_type'=>$gameType])->asArray()->one();
    }

    /**
     * 根据游戏类型获取数据
     * @param $gameType
     * @return array
     */
    public function findByGameType($gameType){
        return self::find()->where('game_type = :game_type',array(":game_type"=>$gameType))->asArray()->all();
    }

    /**
     * 获取所有修改过机率的用户id
     * @return array
     */
    public function getIds(){
        $objs = self::find()->asArray()->all();
        return array_column($objs, 'account_id');
    }

    /**
     *  清空表
     */
    public function clearTable()
    {
        self::deleteAll();
    }


}
