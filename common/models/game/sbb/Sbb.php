<?php

namespace common\models\game\sbb;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use common\models\game\base\GameBase;

class Sbb extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 8;
        $this->gameName        = "SBB";
        $this->chineseGameName = "超级大亨";
        $this->tableLocusDay   = "backend_locus_sbb_day";
        $this->tableLocusMonth = "backend_locus_sbb_month";
        $this->tablePrizeDay   = "backend_prize_sbb_day";
        $this->tableCompare    = "";
        $this->tableMachine    = "fivepk_seo_super_big_boss";
    }

    /**
     * 轨迹日表 实例
     * @return HfhLocus
     */
    public function getModelLocusDay()
    {
        return new SbbLocus();
    }

    /**
     * 比备表实例
     * @return HfhCompare
     */
    public function getModelCompare()
    {
        return "";
    }

    /**
     * 房间表 实例
     * @return SbbRoom
     */
    public function getModelRoom()
    {
        return new SbbRoom();
    }

    /**
     * 机台表 实例
     * @return SbbMachine
     */
    public function getModelMachine()
    {
        return new SbbMachine();
    }

    /**
     *  默认机率表 实例
     * @return SbbDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new SbbDefaultOdds();
    }

    /**
     * prizeday 实例
     * @return HfhPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new SbbPrizeDay();
    }

}
