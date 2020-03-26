<?php

namespace common\models\game\firephoenix;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use common\models\game\base\GameBase;

class Hfh extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 1;
        $this->gameName        = "HFH";
        $this->chineseGameName = "火凤凰";
        $this->tableLocusDay   = "backend_locus_hfh_day";
        $this->tableLocusMonth = "backend_locus_hfh_month";
        $this->tablePrizeDay   = "backend_prize_hfh_day";
        $this->tableCompare    = "backend_compare_hfh";
        $this->tableMachine    = "fivepk_seo_firephoenix";
    }

    /**
     * 轨迹日表 实例
     * @return HfhLocus
     */
    public function getModelLocusDay()
    {
        return new HfhLocus();
    }

    /**
     * 比备表实例
     * @return HfhCompare
     */
    public function getModelCompare()
    {
        return new HfhCompare();
    }

    /**
     * 房间表 实例
     * @return HfhRoom
     */
    public function getModelRoom()
    {
        return new HfhRoom();
    }

    /**
     * 机台表 实例
     * @return HfhMachine
     */
    public function getModelMachine()
    {
        return new HfhMachine();
    }

    /**
     * 新老玩家机率表
     * @return HfhOdds
     */
    public function getModelOdds()
    {
        return new HfhOdds();
    }

    /**
     *  默认机率表 实例
     * @return HfhDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new HfhDefaultOdds();
    }

    /**
     * prizeday 实例
     * @return HfhPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new HfhPrizeDay();
    }

}
