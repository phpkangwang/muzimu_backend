<?php
namespace common\models\game\paman;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use Yii;


class FivepkPlayerPamanWintype extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_paman_wintype';
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
            [['win_type', 'win_type_rate', 'plan_rate', 'plan_rate_base', 'joker_rate_zero', 'joker_rate_one', 'joker_rate_two', 'jp_rate', 'jp_rate_base', 'one_rate', 'two_rate', 'three_rate', 'four_rate', 'rate_award_card_on_location15', 'rate_award_card_on_location3', 'is_big', 'fake_wintype_statistics', 'fake_wintype_statistics_top_limit'], 'integer'],
            [['add_count'], 'number'],
            [['prize_name', 'card_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID主键',
            'account_id' => '玩家ID',
            'prize_name' => '名称',
            'win_type' => '奖型',
            'win_type_rate' => '奖型概率',
            'plan_rate' => '一带二带概率',
            'plan_rate_base' => '一带二带概率基础值',
            'joker_rate_zero' => '随机小奖零张鬼牌概率',
            'joker_rate_one' => '随机小奖一张鬼牌概率',
            'joker_rate_two' => '随机小奖两张鬼牌概率',
            'jp_rate' => 'jp奖概率',
            'jp_rate_base' => 'jp奖概率基础值',
            'one_rate' => '第一手押注',
            'two_rate' => '第二手押注',
            'three_rate' => '第三手押注',
            'four_rate' => '第四手押注',
            'add_count' => '累积值',
            'card_type' => 'Card Type',
            'rate_award_card_on_location15' => '一号位和五号位出现的位置',
            'rate_award_card_on_location3' => '3号位出现的位置',
            'is_big' => '0-小奖1-大奖',
            'fake_wintype_statistics' => '伪奖的概率可配置',
            'fake_wintype_statistics_top_limit' => '伪奖的概率上限',
        ];
    }

    public function updateAllUserOdds($postDatas, $accountIdArr)
    {
        foreach ($postDatas as $key=>$postData)
        {
            FivepkPlayerPamanWintypeDefault::updateAll($postData,['prize_name'=>$key]);
            if( empty($accountIdArr) ){
                self::updateAll($postData,['prize_name'=>$key]);
            }else{
                self::updateAll($postData,['and',['not in','account_id',$accountIdArr],['prize_name'=>$key]]);
            }
        }
        return true;
    }

    /**
     *  获取老玩家几率
     * @param $accountId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getPlayerOdds($accountId)
    {
        $data = self::find()->where(['account_id' => $accountId])->orderBy('sort')->asArray()->all();
        foreach ($data as $key=>$val){
            $data[$key] = $this->Tool->clearFloatZero($val);
        }
        return $data;
    }

    /**
     * 修改老玩家机率
     * @param $postDatas
     * @param $accountIds
     */
    public function updatePlayerOdds($postDatas, $accountIds)
    {
        foreach ($accountIds as $accountId){
            foreach ($postDatas as $key => $data) {
                $obj = self::find()->where('account_id = :account_id and prize_name = :prize_name', array(':account_id' => $accountId, ':prize_name' => $key))->one();
                foreach ($data as $k => $v){
                    $obj->$k = $v;
                }
                $obj->save();
            }
        }
    }

    /**
     * 当老玩家没有数据的时候，首先得初始化数据
     * 每次初始化数据的时候首先得删除掉这个玩家的所有的旧数据
     * @param $accountId
     * @return bool
     */
    public function initUserOdds($accountId){
        self::deleteAll("account_id=:account_id",[':account_id'=>$accountId]);
        $tableName = self::tableName();
        $defaultList = $this->FivepkPlayerPamanWintypeDefault->tableList();
        $insertKey = array();
        $insertStr = "";

        foreach ($defaultList as $key => $val){
            $insertVal = array();
            unset($val['id']);
            foreach ($val as $k=>$v){
                if($key == 0){
                    array_push($insertKey, $k);
                }
                array_push($insertVal,$v);
            }
            //插入用户id这个字段
            if($key == 0) {
                array_push($insertKey, 'account_id');
            }
            array_push($insertVal,$accountId);
            if( !empty($insertVal) ){
                $insertArr[] = " ('".implode("','",$insertVal)."') ";
            }
        }
        $insertStr = implode(" , ",$insertArr);

        $columnStr = implode(",", $insertKey);
        $sql = "insert into {$tableName} ({$columnStr}) values {$insertStr}";
        Yii::$app->game_db->createCommand($sql)->query();


        //这边增加那边也要增加
        $FivepkPlayerPamanSetting = new \common\models\game\paman\FivepkPlayerPamanSetting();
        $FivepkPlayerPamanSetting->initUserOdds($accountId);

        return true;
    }

    public function initUserAllOdds()
    {
        $tableName = self::tableName();
        //Yii::$app->game_db->createCommand("truncate {$tableName}")->query();

        $sql = "insert into 
                        {$tableName} (
                                account_id,
                                prize_name,
                                win_type,
                                win_type_rate,
                                plan_rate,
                                plan_rate_base,
                                joker_rate_zero,
                                joker_rate_one,
                                joker_rate_two,
                                jp_rate,
                                jp_rate_base,
                                one_rate,
                                two_rate,
                                three_rate,
                                four_rate,
                                add_count,
                                buff_count,
                                gap,
                                gap_random,
                                min_bye,
                                max_bye,
                                card_type,
                                rate_award_card_on_location15,
                                rate_award_card_on_location3,
                                is_big,
                                fake_wintype_statistics,
                                fake_wintype_statistics_top_limit,
                                sort) 
                select 
                                fivepk_account.account_id,
                                fivepk_player_paman_wintype_default.prize_name,
                                fivepk_player_paman_wintype_default.win_type,
                                fivepk_player_paman_wintype_default.win_type_rate,
                                fivepk_player_paman_wintype_default.plan_rate,
                                fivepk_player_paman_wintype_default.plan_rate_base,
                                fivepk_player_paman_wintype_default.joker_rate_zero,
                                fivepk_player_paman_wintype_default.joker_rate_one,
                                fivepk_player_paman_wintype_default.joker_rate_two,
                                fivepk_player_paman_wintype_default.jp_rate,
                                fivepk_player_paman_wintype_default.jp_rate_base,
                                fivepk_player_paman_wintype_default.one_rate,
                                fivepk_player_paman_wintype_default.two_rate,
                                fivepk_player_paman_wintype_default.three_rate,
                                fivepk_player_paman_wintype_default.four_rate,
                                fivepk_player_paman_wintype_default.add_count,
                                fivepk_player_paman_wintype_default.buff_count,
                                fivepk_player_paman_wintype_default.gap,
                                fivepk_player_paman_wintype_default.gap_random,
                                fivepk_player_paman_wintype_default.min_bye,
                                fivepk_player_paman_wintype_default.max_bye,
                                fivepk_player_paman_wintype_default.card_type,
                                fivepk_player_paman_wintype_default.rate_award_card_on_location15,
                                fivepk_player_paman_wintype_default.rate_award_card_on_location3,
                                fivepk_player_paman_wintype_default.is_big,
                                fivepk_player_paman_wintype_default.fake_wintype_statistics,
                                fivepk_player_paman_wintype_default.fake_wintype_statistics_top_limit,
                                fivepk_player_paman_wintype_default.sort
                                from  
                                fivepk_account,fivepk_player_paman_wintype_default
                                ";
        Yii::$app->game_db->createCommand($sql)->query();

        //这边增加那边也要增加
        $FivepkPlayerPamanSetting = new \common\models\game\paman\FivepkPlayerPamanSetting();
        $FivepkPlayerPamanSetting->initUserAllOdds();
        return true;
    }
}
