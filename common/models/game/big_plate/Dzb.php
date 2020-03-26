<?php

namespace common\models\game\big_plate;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
use backend\models\Tool;
use Yii;
use common\models\game\base\GameBase;
use common\models\game\FivepkPlayerInfo;

class Dzb extends GameBase
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->gameType        = 3;
        $this->gameName        = "DZB";
        $this->chineseGameName = "大字板";
        $this->tableLocusDay   = "backend_locus_dzb_day";
        $this->tableLocusMonth = "backend_locus_dzb_month";
        $this->tablePrizeDay   = "backend_prize_dzb_day";
        $this->tableCompare    = "backend_compare_dzb";
        $this->tableMachine    = "fivepk_seo_big_plate";
    }

    /**
     * 轨迹日表 实例
     * @return DzbLocus
     */
    public function getModelLocusDay()
    {
        return new DzbLocus();
    }

    /**
     * 比备表实例
     * @return DzbCompare
     */
    public function getModelCompare()
    {
        return new DzbCompare();
    }

    /**
     * 房间表 实例
     * @return DzbRoom
     */
    public function getModelRoom()
    {
        return new DzbRoom();
    }

    /**
     * 机台表 实例
     * @return DzbMachine
     */
    public function getModelMachine()
    {
        return new DzbMachine();
    }

    /**
     * 新老玩家机率表
     * @return DzbOdds
     */
    public function getModelOdds()
    {
        return new DzbOdds();
    }

    /**
     * OddsCount 实例
     * @return DzbOddsCount
     */
    public function getModelOddsCount()
    {
        return new DzbOddsCount();
    }

    /**
     *  默认机率表 实例
     * @return DzbDefaultOdds
     */
    public function getModelDefaultOdds()
    {
        return new DzbDefaultOdds();
    }

    /**
     * prizeday 实例
     * @return DzbPrizeDay
     */
    public function getModelPrizeDay()
    {
        return new DzbPrizeDay();
    }


    /**
     * 获取机率 页面
     * @param $param
     * @return array
     */
    public function oddsIndex($param)
    {
        $MachineModel  = $this->getModelMachine();
        $Tool      = new Tool();
        $time      = time();
        $level     = $param['level'];
        $machine   = $param['machine'];
        $accountId = $param['accountId'];
        $status    = $param['status'];

        $seo_machine_type = null;
        $machine_auto     = null;
        switch ($status) {
            case 1:
                $seo_machine_type = 0;
                break;
            case 2:
                $seo_machine_type = 1;
                break;
            case 3:
                $seo_machine_type = 2;
                break;
            case 4:
                $seo_machine_type = 1;
                $machine_auto     = 1;
                break;
        }

        $where = " machine.status = 1";
        if( $level != "")
        {
            $where .= " and room.room_index = '{$level}'";
        }

        if( $accountId != "")
        {
            $where .= " and machine.account_id = '{$accountId}'";
        }

        if( $machine != "")
        {
            $machine = strtoupper($machine);
            $where .= " and machine.seo_machine_id like %'{$machine}'%";
        }

        if( $seo_machine_type != "")
        {
            $where .= " and machine.seo_machine_type = '{$seo_machine_type}'";
        }

        if( $machine_auto != "")
        {
            $where .= " and machine.machine_auto = '{$machine_auto}'";
        }
        $sql = "select  room.name,machine.*,odds.*
                from {$this->tableMachine} as machine
                left join data_room_info_list as room on room.id = machine.room_info_list_id
                left join odds_dzb as odds on odds.is_default = 2 and odds.odds_type = 2 and odds.odds_type_id = machine.auto_id
                where {$where}
                ";
        $models = Yii::$app->game_db->createCommand($sql)->queryAll();

        //获取所有的用户id
        $accountIds          = array_column($models, 'account_id');
        $accountIds          = array_unique($accountIds);
        $FivepkPlayerInfoObj = new FivepkPlayerInfo();
        $accountObjs         = $FivepkPlayerInfoObj->finds($accountIds);
        $newAccountObjs      = array();
        foreach ($accountObjs as $v) {
            $newAccountObjs[$v['account_id']] = $v;
        }

        $total = [
            '在线' => 0,
            '留机' => 0
        ];

        foreach ($models as $key => $val) {
            unset($models[$key]['roomList']);
            $models[$key]['status'] = $MachineModel->findStatus($val);

            if (isset($val['account_id'])) {
                $models[$key]             = $Tool->clearFloatZero($models[$key]);
                $models[$key]['nickName'] = isset($newAccountObjs[$val['account_id']]['nick_name']) ? $newAccountObjs[$val['account_id']]['nick_name'] : "";
            }
            if (isset($val['seo_machine_type'])) {
                if ($val['seo_machine_type'] == 1) {
                    $total['在线'] += 1;
                } elseif ($val['seo_machine_type'] == 2) {
                    $total['留机'] += 1;
                }
            }

            if (isset($val['reservation_date'])) {
                $models[$key]['reservationStatus'] = $val['reservation_date'] > date($Tool::DATE_USUALLY_FORMAT, $time) ? 1 : 0;
            }
        }

        $data = array(
            'models'             => $models,
            'totalMachineStatus' => $total,
        );
        return $data;
    }


    /**
     *   默认机率
     */
    public function defaultOdds()
    {
        $sql = "
            select room.name as roomName,odds.*
            from odds_dzb as odds
            left join data_room_info_list as room on odds.odds_type_id = room.room_index and room.game = {$this->gameType}
            where odds.is_default = 1 and odds.odds_type = 2 
        ";
        $models = Yii::$app->game_db->createCommand($sql)->queryAll();
        return $models;
    }


}
