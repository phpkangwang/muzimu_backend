<?php
namespace common\models\game\att2;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use backend\models\Tool;
use common\models\game\base\GameBase;

class Att extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 4;
        $this->gameName        = "ATT2";
        $this->chineseGameName = "ATT2";
        $this->tableLocusDay   = "backend_locus_att_day";
        $this->tableLocusMonth = "backend_locus_att_month";
        $this->tablePrizeDay   = "backend_prize_att_day";
        $this->tableCompare    = "backend_compare_att";
        $this->tableMachine    = "fivepk_seo_att2";
    }

    /**
     * 轨迹日表 实例
     * @return AttLocus
     */
    public function getModelLocusDay()
    {
        return new AttLocus();
    }

    /**
     * 比备表实例
     * @return AttCompare
     */
    public function getModelCompare()
    {
        return new AttCompare();
    }

    /**
     * 房间表 实例
     * @return AttRoom
     */
    public function getModelRoom()
    {
        return new AttRoom();
    }

    /**
     * 机台表 实例
     * @return AttMachine
     */
    public function getModelMachine()
    {
        return new AttMachine();
    }

    /**
     *  默认机率表 实例
     * @return AttDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new AttDefaultOdds();
    }

    /**
     * prizeday 实例
     * @return AttPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new AttPrizeDay();
    }

    /**
     * 判断关联那几张表
     * @param stime  筛选条件的开始时间
     * @return string
     */
    public function unionTable($stime)
    {
        $tool = new Tool();
        $rs   = $tool->DayWeekMonth($stime);
        switch ($rs) {
            case "day":
                $table = " backend_locus_att_day";
                break;
            case "week":
                $table = " backend_locus_att_month";
                break;
            case  "month":
                $table = " backend_locus_att_month";
                break;
            default:
                $table = " backend_locus_att_day";
        }
        return $table;
    }
}
