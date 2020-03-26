<?php

namespace common\models\game;

use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\redis\MyRedis;
use backend\models\remoteInterface\remoteInterface;
use backend\models\Tool;
use common\models\DataGameListInfo;
use common\services\GameConfig\GameService;
use common\services\GroupService;
use common\services\ToolService;
use common\models\pay\platform\PayLayerAccount;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "fivepk_account".
 *
 * @property string $account_id
 * @property string $name
 * @property string $password
 * @property string $seoid
 * @property string $udid
 * @property integer $account_type
 * @property string $create_date
 * @property integer $account_info
 * @property string $account_ip
 * @property string $last_login_time
 * @property integer $allowed
 * @property string $address
 */
class FivepkAccount extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_account';
    }

    public $BaseColumn = "account_id,name,seoid,account_type,address,allowed,create_date";

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
            [['account_type', 'allowed'], 'integer'],
            [['create_date', 'last_login_time'], 'safe'],
            [['name', 'udid'], 'string', 'max' => 100],
            [['password'], 'string', 'max' => 255],
            [['seoid'], 'string', 'max' => 25],
            [['account_ip', 'address'], 'string', 'max' => 20],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_id'      => 'Account ID',
            'name'            => 'Name',
            'password'        => 'Password',
            'seoid'           => 'Seoid',
            'udid'            => 'Udid',
            'account_type'    => '0-游客1-普通玩家',
            'create_date'     => 'Create Date',
            'address'         => '地址',
            'account_ip'      => 'Account Ip',
            'last_login_time' => 'Last Login Time',
            'allowed'         => 'Allowed',
        ];
    }


    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->save()) {
                return $this->attributes;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 根据$seoIds获取所有的用户id
     * @param $seoIds
     */
    public function getByWhere($where)
    {
        $data = self::find()->where($where)->asArray()->all();
        return array_column($data, 'account_id');
    }

    /**
     * 获取某一段时间内玩家的数量
     * @param $stime
     * @param $etime
     * @return int|string
     */
    public function findByTime($stime, $etime)
    {
        return self::find()->where('create_date >= :stime and create_date <= :etime', array(':stime' => $stime, ':etime' => $etime))->count();
    }

    /**
     * 根据账户查找玩家信息
     * @param $account
     * @return array
     */
    public function findByAccount($account)
    {
        return FivepkAccount::find()->where(['name' => $account])->asArray()->one();
    }


    /**
     * 关联玩家详细信息
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(), ['account_id' => 'account_id']);
    }

    /**
     * 关联捕鱼玩家详细信息
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerInfoFish()
    {
        return $this->hasOne(FivepkPlayerInfoFish::className(), ['account_id' => 'account_id'])->select('account_id,win_score_final,play_score_final');
    }

    /**
     * 根据popCode获取信息
     * @param $popCodeArr
     * @return array  返回account_id二维数组
     */
    public function findBySeoId($popCodeArr)
    {
        $data = array();
        if (!empty($popCodeArr)) {
            $inStr = "'" . implode("','", $popCodeArr) . "'";
            $sql   = "select account_id from " . self::tableName() . " where seoid in ({$inStr})";
            $data  = Yii::$app->game_db->createCommand($sql)->queryAll();
            $data  = array_column($data, 'account_id');
        }
        return $data;
    }


    //老玩家列表：在线0人，在玩0人，留机0人，今日活跃会员20000，总人数20146
    public function getCount($popCodeArr)
    {
        $sql = " select * 
                from fivepk_account a,fivepk_player_info i 
                where a.account_id = i.account_id 
                and i.reservation_machine_id <> ''";
        //查询当前玩家的活跃玩家 -- xxxxxx
        $getActiveAccountWhere = " 1";
        $activeCount           = $this->getActiveAccount($getActiveAccountWhere);
        //查询当前玩家的留机人数
        $ReservationMachineWhere = " 1";
        $reservationCount        = $this->FivepkPlayerInfo->getReservationMachineCount($ReservationMachineWhere);
        //获取一共有多少个用户
        $accountNumWhere = " 1";
        $total           = $this->accountNum($accountNumWhere);
        $result          = [
            'total'       => $total,
            'active'      => $activeCount,
            'reservation' => $reservationCount,
        ];
        return $result;
    }

    public static function getExperienceCount()
    {
        $arr = [
            '在玩人数'   => FivepkPlayerInfo::find()->filterWhere(['like', 'seo_machine_id', 'TY'])->count(),
            '今日得钻人数' => FivepkPlayerInfo::find()->filterWhere(['>', 'today_experience_contribution', 0])->count(),
            '今日总送钻'  => FivepkPlayerInfo::find()->sum('today_experience_contribution') / 100,
        ];
        return $arr;
    }

    //按照条件下获取活跃用户
    public function getActiveAccount($where)
    {
        return self::find()->where($where)->andWhere('last_login_time>:last_login_time', array(':last_login_time' => date('Y-m-d')))->count();
    }

    /**
     * 获得在线状态
     * @return null|string
     */
    public function getStatus()
    {
        $status = null;
        switch ($this->playerInfo->is_online) {
            case 0:
                if ($this->allowed == 1)
                    $status = '封禁';
                else
                    $status = '';
                break;
            case 1:
                $status = '在线';
                break;
        }
        return $status;
    }

    public function switchStatus($is_online, $allowed)
    {
        $status = null;
        switch ($is_online) {
            case 0:
                if ($allowed == 1)
                    $status = '封禁';
                else
                    $status = '';
                break;
            case 1:
                $status = '在线';
                break;
        }
        return $status;
    }

    /**
     * 分页
     * @return array
     */
    public function page($pageNo, $pageSize, $where, $orderBy = "account_id desc")
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo - 1) * $pageSize;
        return self::find()->where($where)->offset($offset)->limit($limit)->orderBy($orderBy)->asArray()->all();
    }

    /**
     *  获取最大条数
     */
    public function accountNum($where)
    {
        return self::find()->where($where)->count();
    }


    /**
     * 分页
     * @return array
     */
    public function UserDiamondInfoPage($pageNo, $pageSize, $where)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo - 1) * $pageSize;
        return self::find()->joinWith('playerInfo')->where($where)->offset($offset)->limit($limit)->orderBy('fivepk_account.last_login_time DESC')->asArray()->all();
    }

    /**
     *  获取最大条数
     */
    public function UserDiamondInfoCount($where)
    {
        return self::find()->joinWith('playerInfo')->where($where)->count();
    }

    /**
     * 根据id查询多条数据
     * @param $ids array
     * @return array
     */
    public function finds($ids)
    {
        return self::find()->where(['in', 'account_id', $ids])->asArray()->all();
    }

    /**
     * 根据id查询多条数据
     * @param $ids array
     * @param $seoIds array
     * @return array
     */
    public function findsByIdSeoId($ids, $seoIds)
    {
        return self::find()->where(['in', 'account_id', $ids])->andWhere(['in', 'seoid', $seoIds])->asArray()->all();
    }

    /**
     * 查找某一天用户注册的数量
     * @param $time 2019-01-01
     * @return array
     */
    public function findRegistNum($time)
    {
        $stime = $time . " 00:00:00";
        $etime = $time . " 23:59:59";
        return self::find()->where('create_date >= :stime and create_date <= :etime', array(":stime" => $stime, ":etime" => $etime))->count();
    }

    public function findPage($params)
    {
        $pageNo        = $params['pageNo'];
        $pageSize      = $params['pageSize'];
        $popCode       = $params['popCode'];
        $accountId     = $params['accountId'];
        $machine       = $params['machine'];
        $register_time = $params['register_time'];
        $sort          = $params['sort'];
        $sortType      = $params['sortType'];
        $popCodes      = $params['popCodes'];

        $PageRs = Tool::page($pageNo, $pageSize);
        $limit  = $PageRs['limit'];
        $offset = $PageRs['offset'];

        $where = "  fivepk_account.seoid in ({$popCodes})";

        if (!empty($popCode)) {
            $where .= " and fivepk_account.seoid = '{$popCode}'";
        }
        if (!empty($accountId)) {
            $where .= " and fivepk_account.account_id = '{$accountId}'";
        }
        if (!empty($machine)) {
            $where .= " and (fivepk_player_info.reservation_machine_id like '%{$machine}%' 
                            OR fivepk_player_info.seo_machine_id like '%{$machine}%' 
                            OR fivepk_player_info.offline_machine_id like '%{$machine}%' 
            ) ";
        }
        if (!empty($register_time)) {
            $register_stime = $register_time . " 00:00:00";
            $register_etime = $register_time . " 23:59:59";
            $where          .= " and fivepk_account.create_date >= '{$register_stime}' and fivepk_account.create_date < '{$register_etime}'";
        }
        if (!empty($sort)) {
            $orderBy = "{$sort} {$sortType}";
        } else {
            //默认排序。先显示在线，然后是留机，然后是强退留机，在显示最近登录时间的
            $orderBy = "fivepk_player_info.is_online desc,seo_machine_id desc,reservation_machine_id desc,offline_machine_id desc,fivepk_account.last_login_time desc";
        }

        $select = $this->getBaseColumnSelect("fivepk_account");
        $sql    = "
            select {$select},fivepk_player_info.*
            from fivepk_account
            left join fivepk_player_info  on  fivepk_account.account_id = fivepk_player_info.account_id
            where {$where}
            order by {$orderBy}
            limit {$limit} 
            offset {$offset} 
        ";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }

    /**
     * 获取基础数据的选择
     * @param $pre
     */
    public function getBaseColumnSelect($pre)
    {
        $accountColumn    = explode(",", $this->BaseColumn);
        $newAccountColumn = array();
        foreach ($accountColumn as $val) {
            array_push($newAccountColumn, $pre . "." . $val);
        }
        return implode(",", $newAccountColumn);
    }

    /**
     *  获取所有的推广号
     * @return mixed
     */
    public function getAllSeoid()
    {
        $sql = "select * from fivepk_account group by seoid";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }

    /**
     * 修改用户层级
     * @param $accountId
     * @param $payLayerId
     * @return bool
     */
    public static function updatePayLayer($accountId, $payLayerId)
    {
        try {
            $obj = self::findOne($accountId);
            if (!isset($obj->backend_zdl_account_id) || !isset($obj->pay_layer)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            //检查层级必须是在用户总代理的头上
            $PayLayerAccountModel = new PayLayerAccount();
            $payLayerArr          = $PayLayerAccountModel->findByAccount($obj->backend_zdl_account_id);

            if (empty($payLayerArr)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $idsByPayLayerArr = array_column($payLayerArr, 'id', 'id');
            if (!isset($idsByPayLayerArr[$payLayerId])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $data = $obj->add(['pay_layer' => $payLayerId]);

            return $data;

        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改某个层级的用户id为1(默认层级id)
     * @param $payLayerId
     * @return int
     */
    public static function initAccountPayLayer($payLayerId)
    {
        return self::updateAll(['pay_layer' => 1], ['pay_layer' => $payLayerId]);
    }

    private static $userInfoArr;

    //获取用户信息 这里存放redis 所以不是实时的
    public static function getUserInfoForUserId($userId)
    {

        if (isset(self::$userInfoArr[$userId])) {
            return self::$userInfoArr[$userId];
        }

        $redisName = 'userInfoArr';
        $myRedis   = new MyRedis();
        $data      = $myRedis->readCacheHash($redisName, $userId);
        if (empty($data)) {

            $data = self::find()->select('account_id,seoid')->where(['account_id' => $userId])->asArray()->one();
            $myRedis->writeCacheHash($redisName, $userId, $data);
        }
        self::$userInfoArr[$userId] = $data;

        return self::$userInfoArr[$userId];
    }


    public static function getOnlinePlayer($popCodeArr)
    {
        //查询太慢添加缓存
        $redisKey  = "game:UserOnlineInfo:" . json_encode($popCodeArr);
        $redisObj  = new MyRedis();
        $redisData = $redisObj->get($redisKey);
        if ( empty($redisData) ) {
            $FivepkPlayerInfoObj = new FivepkPlayerInfo();
            $count               = $FivepkPlayerInfoObj->getOnlinePlayer($popCodeArr);

            $FivepkAccountObj = new FivepkAccount();
            $onlineUserIds = $FivepkAccountObj->findBySeoId($popCodeArr);

            //获取所有开启的游戏
            $DataGameListInfoObj = new DataGameListInfo();
            $OpenGame            = $DataGameListInfoObj->getOpenGame();

            //获取所有在线的用户
            $remoteInterfaceObj = new remoteInterface();
            $contents           = $remoteInterfaceObj->getOnlinePlayer();

            $status        = array();//在线玩家游戏状态 [status] => Array([43100] => 大字板列表[306] => 火凤凰列表)
            $arr           = array();//在线玩家id列表   [arr] => Array([0] => 43100[1] => 306)
            $arrOfGameType = array();//游戏在线玩家数量
            $count['playing_user'] = 0;      //本地查询出来的在线玩家不准确，必须用接口调用的值
            //这里的值和数据库里的is_online可能不一样 不必理会

            //删除不是自己推广号的账号
            foreach ($contents['data'] as $key => $val) {
                if (!in_array($val['accountId'], $onlineUserIds)) {
                    unset($contents['data'][$key]);
                    continue;
                }


                $status[$val['accountId']] = $FivepkPlayerInfoObj->getGameStaus($val, $OpenGame);

                array_push($arr, $val['accountId']);

                if (!isset($arrOfGameType[$val['gameType']]['count'])) {
                    $arrOfGameType[$val['gameType']]['count'] = 0;
                }

                if ($val['playSpace'] == 2 || $val['playSpace'] == 3 || $val['playSpace'] == 4 || $val['playSpace'] == 5) {
                    $count['playing_user']                    += 1;
                    $arrOfGameType[$val['gameType']]['count'] += 1;
                }

            }

            $countOfGameType = [];
            foreach ($OpenGame as $value) {
                $countOfGameType[$value['game_name']] = Tool::examineEmpty($arrOfGameType[$value['game_number']]['count'], 0);
            }

            //在线玩家的用户id总数
            $count['online'] = count($arr);

            //获取这些用户的设备类型
            $count['loginSystemOnline'] = $FivepkAccountObj->LoginSystemSum($arr);

            $rsData = ['status' => $status, 'arr' => $arr, 'count' => $count, 'countOfGameType' => $countOfGameType];
            $redisObj->set($redisKey, json_encode($rsData));
        } else {
            return json_decode($redisData, true);
        }
        return $rsData;
    }

    /**
     *   每种登录类型用户总数
     */
    public function LoginSystemSum($acountIds){
        $in = "'" . implode("','", $acountIds) . "'";
        $sql = "
            select login_system,count(account_id) as num
            from fivepk_account
            where account_id in({$in})
            group by login_system
        ";
        $objs = Yii::$app->game_db->createCommand($sql)->queryAll();
        $rs = array();
        foreach ($objs as $val){
            $rs[$val['login_system']] = (int)$val['num'];
        }
        return $rs;
    }
}
