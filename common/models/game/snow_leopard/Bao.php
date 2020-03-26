<?php

namespace common\models\game\snow_leopard;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use common\models\game\base\GameBase;

class Bao extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 11;
        $this->gameName        = "BAO";
        $this->chineseGameName = "雪豹";
        $this->tableLocusDay   = "backend_locus_bao_day";
        $this->tableLocusMonth = "backend_locus_bao_month";
        $this->tablePrizeDay   = "backend_prize_bao_day";
        $this->tableCompare    = "";
        $this->tableMachine    = "snow_leopard_seo_machine";
    }

    /**
     * 轨迹日表 实例
     * @return BaoLocus
     */
    public function getModelLocusDay()
    {
        return new BaoLocus();
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
     * @return Baomachine
     */
    public function getModelMachine()
    {
        return new BaoMachine();
    }

    /**
     * 新老玩家机率表
     * @return BaoOdds
     */
    public function getModelOdds()
    {
        return new BaoOdds();
    }

    /**
     *  默认机率表 实例
     * @return BaoDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new BaoDefaultOdds();
    }

    /**
     * prizeday 实例
     * @return BaoPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new BaoPrizeDay();
    }

}
