<?php

namespace common\models\game\big_shark;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use common\models\game\base\GameBase;

class Dbs extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 2;
        $this->gameName        = "DBS";
        $this->chineseGameName = "大白鲨";
        $this->tableLocusDay   = "backend_locus_dbs_day";
        $this->tableLocusMonth = "backend_locus_dbs_month";
        $this->tablePrizeDay   = "backend_prize_dbs_day";
        $this->tableCompare    = "backend_compare_dbs";
        $this->tableMachine    = "fivepk_seo_bigshark";
    }

    /**
     * 轨迹日表 实例
     * @return DbsLocus
     */
    public function getModelLocusDay()
    {
        return new DbsLocus();
    }

    /**
     * 比备表实例
     * @return DbsCompare
     */
    public function getModelCompare()
    {
        return new DbsCompare();
    }

    /**
     * 房间表 实例
     * @return DbsRoom
     */
    public function getModelRoom()
    {
        return new DbsRoom();
    }

    /**
     * 机台表 实例
     * @return DbsMachine
     */
    public function getModelMachine()
    {
        return new DbsMachine();
    }

    /**
     *  默认机率表 实例
     * @return DbsDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new DbsDefaultOdds();
    }

    /**
     * prizeday 实例
     * @return DbsPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new DbsPrizeDay();
    }

}
