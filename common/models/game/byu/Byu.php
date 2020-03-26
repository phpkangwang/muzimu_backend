<?php

namespace common\models\game\byu;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use common\models\game\base\GameBase;

class Byu extends GameBase
{
    public $tableRoomFish;
    public $tableFish;
    public $tableRoomFishRate;
    public $tablePlayerOdds;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType          = 13;
        $this->gameName          = "BYU";
        $this->chineseGameName   = "欢乐捕鱼";
        $this->tableLocusDay     = "backend_locus_byu_day";
        $this->tableLocusMonth   = "backend_locus_byu_day";
        $this->tablePrizeDay     = "backend_prize_byu_day";
        $this->tableCompare      = "";
        $this->tableMachine      = "";
        $this->tableRoom         = "room_config";//房间
        $this->tableFish         = "fish_config";//鱼的配置
        $this->tableRoomFish     = "room_fish_config";//房间配置
        $this->tableRoomFishRate = "room_fish_rate_config";//房间奖池
        $this->tablePlayerOdds   = "fish_player_info";  //玩家机率
    }

    /**
     * 轨迹日表 实例
     * @return string
     */
    public function getModelLocusDay()
    {
        return new ByuLocus();
    }

    /**
     * 比备表实例
     * @return string
     */
    public function getModelCompare()
    {
        return "";
    }

    /**
     * 机台表 实例
     * @return string
     */
    public function getModelMachine()
    {
        return new ByuRoom();
    }

    /**
     * 所有鱼 基本信息实例
     * @return ByuFish
     */
    public function getModelFish()
    {
        return new ByuFish();
    }

    /**
     * 房间表 实例
     * @return ByuRoom
     */
    public function getModelRoom()
    {
        return new ByuRoom();
    }

    /**
     * 房间鱼表的配置 实例
     * @return ByuRoomFish
     */
    public function getModelRoomFish()
    {
        return new ByuRoomFish();
    }

    /**
     * 房间鱼表的配置奖池 实例
     * @return ByuRoomFishRate
     */
    public function getModelRoomFishRate()
    {
        return new ByuRoomFishRate();
    }

    /**
     * 默认机率 实例
     * @return ByuPlayerOdds
     */
    public function getModelPlayerOdds()
    {
        return new ByuPlayerOdds();
    }

    /**
     * 默认机率 实例
     * @return ByuDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new ByuDefaultOdds();
    }

    /**
     * prizeday 实例
     * @return ByuPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new ByuPrizeDay();
    }


    ###############################  重写 方法  主要针对于 所有游戏不同的地方 #############################
    public function getRoomList()
    {
        $ByuRoomModel = new ByuRoom();
        return $ByuRoomModel->getRoomList();
    }

    /**
     *   默认机率
     */
    public function defaultOdds()
    {
        $getModelDefaultOddsObj = $this->getModelDefaultOdds();
        $obj                    = $getModelDefaultOddsObj->findDefault(self::DEFAULT_ODDS_MACHINE);
        return $obj;
    }

    /**
     *  机率调控页面
     * @param $param
     * @return array
     * @throws
     */
    public function oddsIndex($param)
    {
        $roomId                = $param['level'];
        $getModelRoomFishModel = $this->getModelRoomFish();
        return $getModelRoomFishModel->findByRoom($roomId);
    }


    /**
     * 判断关联那几张表
     * @param stime  筛选条件的开始时间
     * @return string
     */
    public function unionTable($stime)
    {
//        $tool = new Tool();
//        $rs   = $tool->DayWeekMonth($stime);
//        switch ($rs) {
//            case "day":
//                $table = " backend_locus_byu_day";
//                break;
//            case "week":
//                $table = " backend_locus_byu_month";
//                break;
//            case  "month":
//                $table = " backend_locus_byu_month";
//                break;
//            default:
//                $table = " backend_locus_byu_day";
//        }
        //捕鱼的轨迹总表只有一张
        return "backend_locus_byu_day";
    }

    /**
     * 获取各个游戏的奖  在捕鱼里面奖就是鱼
     * @param $gameType
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public function getPrizeTypeList($gameType)
    {
        $FishModel = $this->getModelFish();
        return $FishModel->tableList();
    }

    /**
     *  查找这个游戏一共有多少个场次
     * @return array|void
     */
    public function getRoom()
    {
        $data = $this->getRoomList();
        foreach ($data as $key => $val){
            $data[$key]['room_index'] = $val['id'];
        }
        return $data;
    }
}
