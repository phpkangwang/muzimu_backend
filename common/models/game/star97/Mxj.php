<?php

namespace common\models\game\star97;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use common\models\game\base\GameBase;

class Mxj extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 6;
        $this->gameName        = "MXJ";
        $this->chineseGameName = "明星97";
        $this->tableLocusDay   = "backend_locus_mxj_day";
        $this->tableLocusMonth = "backend_locus_mxj_month";
        $this->tablePrizeDay   = "backend_prize_mxj_day";
        $this->tableCompare    = "";
        $this->tableMachine    = "machine_list_star97";
    }

    /**
     * 轨迹日表 实例
     * @return MxjLocus
     */
    public function getModelLocusDay()
    {
        return new MxjLocus();
    }

    /**
     * 比备表实例
     * @return
     */
    public function getModelCompare()
    {
        return "";
    }

    /**
     * 机台表 实例
     * @return MxjMachine
     */
    public function getModelMachine()
    {
        return new MxjMachine();
    }

    /**
     *  默认机率表 实例
     * @return MxjDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new MxjDefaultOdds();
    }

    /**
     * prizeday 实例
     * @return MxjPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new MxjPrizeDay();
    }

    /**
     * 根据类型获取某一个奖池的 实例
     * @param $type
     * @return RoomRewardPoolOneStar97|RoomRewardPoolThreeStar97|RoomRewardPoolTwoStar97|string
     */
    public function getModelRoomRewardPool($type)
    {
        switch ($type){
            case 1:
                return new RoomRewardPoolOneStar97();
            case 2:
                return new RoomRewardPoolTwoStar97();
            case 3:
                return new RoomRewardPoolThreeStar97();
            default:
                return "";
        }
    }
}
