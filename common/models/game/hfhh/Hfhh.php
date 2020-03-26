<?php

namespace common\models\game\hfhh;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use common\models\game\base\GameBase;

class Hfhh extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 14;
        $this->gameName        = "HFHH";
        $this->chineseGameName = "H5火凤凰";
        $this->tableLocusDay   = "backend_locus_hfhh_day";
        $this->tableLocusMonth = "backend_locus_hfhh_month";
        $this->tablePrizeDay   = "backend_prize_hfhh_day";
        $this->tableCompare    = "backend_compare_hfhh";
        $this->tableMachine    = "fivepk_seo_firephoenixh";
    }

    /**
     * 轨迹日表 实例
     * @return HfhhLocus
     */
    public function getModelLocusDay()
    {
        return new HfhhLocus();
    }

    /**
     * 比备表实例
     * @return HfhhCompare
     */
    public function getModelCompare()
    {
        return new HfhhCompare();
    }

    /**
     * 房间表 实例
     * @return HfhhRoom
     */
    public function getModelRoom()
    {
        return new HfhhRoom();
    }


    /**
     * 机台表 实例
     * @return HfhhMachine
     */
    public function getModelMachine()
    {
        return new HfhhMachine();
    }

    /**
     *  默认机率表 实例
     * @return HfhhDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new HfhhDefaultOdds();
    }

    /**
     * prizeday 实例
     * @return HfhhPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new HfhhPrizeDay();
    }

}
