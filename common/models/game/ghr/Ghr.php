<?php

namespace common\models\game\ghr;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use common\models\game\base\GameBase;

class Ghr extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 12;
        $this->gameName        = "GHR";
        $this->chineseGameName = "黄金赛马";
        $this->tableLocusDay   = "backend_locus_ghr_day";
        $this->tableLocusMonth = "backend_locus_ghr_month";
        $this->tablePrizeDay   = "backend_prize_ghr_day";
        $this->tableCompare    = "";
        $this->tableMachine    = "gold_horse_race_room_config";
    }

    /**
     * 轨迹日表 实例
     * @return BaoLocus
     */
    public function getModelLocusDay()
    {
        return new GhrLocus();
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
        return new GhrMachine();
    }

    /**
     * prizeday 实例
     * @return BaoPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new GhrPrizeDay();
    }

}
