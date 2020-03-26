<?php
namespace common\models\game;

use backend\models\MyException;
use Yii;

/**
 * This is the model class for table "fivepk_player_info".
 *
 * @property string $account_id
 * @property string $nick_name
 * @property integer $pic
 * @property integer $is_online
 * @property integer $coin
 * @property integer $score
 * @property integer $guide
 * @property integer $win_history
 * @property integer $win_best
 * @property integer $is_first_recharge
 * @property integer $today_contribution
 * @property string $total_contribution
 * @property string $experience_contribution
 * @property string $reservation_contribution
 * @property integer $score_guest
 * @property integer $nick_name_count
 * @property string $seo_machine_id
 * @property string $reservation_machine_id
 * @property string $offline_machine_id
 * @property integer $prefab_jail
 * @property integer $prefab_jail_big_plate
 * @property integer $prefab_star_jail
 * @property integer $prefab_jail_big_shark
 * @property integer $win_point
 * @property integer $play_point
 * @property integer $play_math
 * @property integer $total_play
 * @property integer $total_win_point
 * @property integer $total_play_point
 * @property string $switch_changed_time
 * @property integer $today_experience_contribution
 * @property integer $is_star97_newer
 * @property integer $star97_play_count
 * @property integer $star97_newer_interval_count
 * @property integer $star97_newer_current_index
 * @property string $last_enter97_time
 * @property integer $four_of_a_kind_gift_count
 * @property integer $four_of_a_kind_gift_count_random
 */
class FivepkPlayerInfo extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_info';
    }
    public $BaseColumn = "account_id,nick_name,is_online,today_contribution,total_contribution";


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
            [['account_id', 'nick_name'], 'required'],
            [['account_id', 'pic', 'is_online', 'coin', 'score', 'guide', 'win_history', 'win_best', 'is_first_recharge', 'today_contribution', 'total_contribution', 'experience_contribution', 'reservation_contribution', 'score_guest', 'nick_name_count', 'prefab_jail', 'prefab_jail_big_plate', 'win_point', 'play_point', 'play_math', 'total_play', 'total_win_point', 'total_play_point', 'switch_changed_time','today_experience_contribution','prefab_jail_big_shark','is_star97_newer','star97_play_count','star97_newer_interval_count','star97_newer_current_index'], 'integer'],
            [['nick_name','last_enter97_time'], 'string', 'max' => 25],
            [['seo_machine_id', 'reservation_machine_id', 'offline_machine_id'], 'string', 'max' => 200],
            [['nick_name'], 'unique'],
            [['four_of_a_kind_gift_count','four_of_a_kind_gift_count_random'],'integer','on'=>'four_kinds'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_id' => 'Account ID',
            'nick_name' => 'Nick Name',
            'pic' => 'Pic',
            'is_online' => '0-离线1-在线',
            'coin' => 'Coin',
            'score' => 'Score',
            'guide' => 'Guide',
            'win_history' => 'Win History',
            'win_best' => 'Win Best',
            'is_first_recharge' => '是否首充true首充过false没有首充过',
            'today_contribution' => '今日贡献度',
            'total_contribution' => '总贡献度',
            'experience_contribution' => '总送钻',
            'reservation_contribution' => '留机总贡献度',
            'score_guest' => 'Score Guest',
            'nick_name_count' => 'Nick Name Count',
            'seo_machine_id' => '在线机台',
            'reservation_machine_id' => '留机机台',
            'offline_machine_id' => '强退留机机台',
            'prefab_jail' => 'Prefab Jail',
            'prefab_jail_big_shark'=>'大白鲨开关',
            'prefab_jail_big_plate' => '大字板开关',
            'win_point' => 'Win Point',
            'play_point' => 'Play Point',
            'play_math' => 'Play Math',
            'total_play' => '总玩局数',
            'total_win_point' => '总赢分数',
            'total_play_point' => '总玩分数',
            'switch_changed_time' => '开关修改时间',
            'today_experience_contribution' => '今日送钻',
            'is_star97_newer'=>'是否是97新玩家',
            'star97_play_count'=>'97总玩局数',
            'star97_newer_interval_count'=>'97新人奖间隔局数',
            'star97_newer_current_index'=>'97新人补奖当前索引',
            'last_enter97_time'=>'最后进97机台时间',
            'four_of_a_kind_gift_count'=>'强补间隔累计值',
            'four_of_a_kind_gift_count_random'=>'强补间隔随机',
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
     * 获得机台编号
     * @return string
     */
    public function getMachineId()
    {
        $result = [
            0=>(!empty($this->reservation_machine_id))?'留：'.$this->reservation_machine_id.',':'',
            1=>(!empty($this->seo_machine_id))?'玩：'.$this->seo_machine_id.',':'',
        ];
        return $result[0].$result[1];
    }
    public function SwitchMachineId($reservation_machine_id, $seo_machine_id){
        $result = [
            0=>(!empty($reservation_machine_id))?'留：'.$reservation_machine_id.',':'',
            1=>(!empty($seo_machine_id))?'玩：'.$seo_machine_id.',':'',
        ];
        return $result[0].$result[1];
    }

    public function getExperienceMachineId()
    {
        $result = [0=>null,1=>null];
        if(strpos($this->reservation_machine_id,'TY')!==false || strpos($this->seo_machine_id,'TY')!== false) {
            $result = [
                0 => (!empty($this->reservation_machine_id)) ? '留：' . $this->reservation_machine_id . ',' : '',
                1 => (!empty($this->seo_machine_id)) ? '玩：' . $this->seo_machine_id . ',' : '',
            ];
        }
        return $result[0].$result[1];
    }


    public static function is_field($game_id = '',$games = []){

        $game_list = \yii\helpers\ArrayHelper::map($games, 'id','game_number') ;
        if(in_array($game_id,$game_list)){
            return  1 ;
        }
        return  0;

    }

    /**
     * 分页
     * @return array
     */
    public function page($pageNo, $pageSize, $where)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->where($where)->offset($offset)->orderBy('account_id')->limit($limit)->asArray()->all();
    }
    /**
     * 分页数量
     * @return array
     */
    public function pageCount( $where )
    {
        return self::find()->where($where)->count();
    }

    public function getPlayerListByNick($nick)
    {
        return self::find()->select(['nick_name','account_id'])->filterWhere(['like','nick_name',$nick])->asArray()->all();
    }

    /**
     *  查找账户基本信息
     */
    public function findBase($accountId)
    {
        return self::find()->where("account_id=:accountId",[':accountId'=>$accountId])->asArray()->one();
    }

    public function getNickNameById($accountId)
    {
        $obj = self::find()->where("account_id=:accountId",[':accountId'=>$accountId])->asArray()->one();
        return $obj['nick_name'];
    }

    /**
     * 根据id查询多条数据
     * @param $ids array
     * @return array
     */
    public function finds($ids)
    {
        $data = array();
        if( !empty($ids) )
        {
            $inStr = "'".implode("','", $ids)."'";
            $sql = "select * from ".self::tableName()." where account_id in ({$inStr})";
            $data = Yii::$app->game_db->createCommand($sql)->queryAll();
        }
        return $data;
        //return self::find()->filterWhere(['in','account_id',$ids])->asArray()->all();
    }

    /**
     *  修改表字段
     * @param $accountIdArr
     * @param $params
     */
    public function updates($accountIdArr, $params)
    {
        return self::updateAll($params,['in','account_id',$accountIdArr]);
    }

    /**
     *  根据条件获取六级机台数量
     * @param $where
     */
    public function getReservationMachineCount($where)
    {
        return self::find()->where($where)->andWhere('reservation_machine_id<>""')->count();
    }

    /**
     *  获取留机机台书记
     *  @param $where
     * @return Array
     */
    public function getReservationMachine()
    {
        return self::find()->where('reservation_machine_id <> "" ')->asArray()->all();
    }

    /**
     * 获取在线玩家统计
     * @param $popCodeArr  玩家推广码
     * @return array
     */
    public function getOnlinePlayer($popCodeArr)
    {
        $inStr = "'".implode("','", $popCodeArr)."'";
        $sql = " select a.account_id,a.login_system,a.last_login_time,i.is_online,i.seo_machine_id,i.reservation_machine_id
                from fivepk_account a,fivepk_player_info i 
                where a.account_id = i.account_id 
                and seoid in ({$inStr})";
        $objs = Yii::$app->game_db->createCommand($sql)->queryAll();
        $rs['total'] = count($objs); //总人数
        $rs['active'] = 0;           //活跃人数
        $rs['reservation'] = 0;      //留机人数
        $rs['playing_user'] = 0;     //在玩人数
        $rs['online'] = 0;          //在线人数
        $rs['loginSystem'] = array();
        $time = strtotime(date("Y-m-d"),time());
        foreach ($objs as $val){
            if( strtotime($val['last_login_time']) > $time ){
                $rs['active'] ++;
            }
            if( !empty($val['reservation_machine_id']) ){
                $rs['reservation'] ++;
            }
            if( !empty($val['seo_machine_id']) ){
                $rs['playing_user'] ++;
            }
            if( $val['is_online'] == 1 ){
                $rs['online'] ++;
            }
            $rs['loginSystem'][$val['login_system']] = isset( $rs['loginSystem'][$val['login_system']] ) ? $rs['loginSystem'][$val['login_system']] : 0;
            $rs['loginSystem'][$val['login_system']] += 1;
       }
        return $rs;
    }

    /**
     *  获取在线玩家游戏状态
     * @param $onlineObj  Array([accountId] => 424,[gameType] => 1,[playSpace] => 3,[paramOne] => 0,[paramTwo] => 0,[paramThree] => 0,[bet] => 200)
     * @param $OpenGame  所有开启的游戏
     * @return string
     */
    public function getGameStaus($onlineObj, $OpenGame)
    {
        $GameStaus = "";
        if( !empty($onlineObj) ){
            $gameType = [
                1 => '大厅',
                2 => '列表',
                3 => '游戏中',
                4 => '比倍',
                5 => '连庄',
                6 => '不活跃',
            ];
            $gameName = "";
            foreach ($OpenGame as $val){
                if($val['game_number'] == $onlineObj['gameType']){
                    if( $val['game_number'] != 0 ){
                        $gameName = $val['game_name'];
                    }
                }
            }
            $GameStaus  = $gameName . $gameType[$onlineObj['playSpace']];
        }
        return $GameStaus;
    }
}
