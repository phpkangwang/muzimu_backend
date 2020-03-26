<?php

namespace common\models\game\paman;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use common\models\game\base\GameBase;

class Pam extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 10;
        $this->gameName        = "PAM";
        $this->chineseGameName = "PAMAN";
        $this->tableLocusDay   = "backend_locus_pam_day";
        $this->tableLocusMonth = "backend_locus_pam_month";
        $this->tablePrizeDay   = "backend_prize_pam_day";
        $this->tableCompare    = "";
        $this->tableMachine    = "fivepk_seo_paman";
    }

    /**
     * 轨迹日表 实例
     * @return PamLocus
     */
    public function getModelLocusDay()
    {
        return new PamLocus();
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
     * 房间表 实例
     * @return FivepkRoomPaman
     */
    public function getModelRoom()
    {
        return new PamRoom();
    }

    /**
     * 机台表 实例
     * @return PamMachine
     */
    public function getModelMachine()
    {
        return new PamMachine();
    }

    /**
     * 机台表 eWintype 实例
     * @return PamMachineWintype
     */
    public function getModelMachineWintype()
    {
        return new PamMachineWintype();
    }

    /**
     *  默认机率表 实例
     * @return HfhDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new PamMachineWintypeDefault();
    }

    /**
     * prizeday 实例
     * @return PamPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new PamPrizeDay();
    }

}
