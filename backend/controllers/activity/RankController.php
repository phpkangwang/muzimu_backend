<?php


namespace backend\controllers\activity;
use backend\models\MyException;
use backend\models\Tool;
use common\models\activity\rank\RankAwardAccount;
use common\models\game\FivepkPlayerInfo;

class RankController extends \backend\controllers\MyController
{
    private $set=[];
    /**
     *   排行列表
     */
    public function actionRankList()
    {
        try {
            $RankAwardData = new \common\models\activity\rank\RankAwardData();
            $this->setData($RankAwardData->getList($this->get));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   排行列表 增加/修改
     */
    public function actionRankAdd()
    {
        try {
            $RankAwardData = new \common\models\activity\rank\RankAwardData();
            $this->loginInfo;
            $this->setData($RankAwardData->rankAdd($this->post,$this->loginInfo['name']));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  排行榜放奖记录
     */
    public function actionRankRecord()
    {
        if ( !isset($this->get['pageNo']) || !isset($this->get['pageSize']) || !isset($this->get['rankingType']) || !isset($this->get['stime']) ) {
            throw new MyException(ErrorCode::ERROR_PARAM);
        }
        $pageNo   = $this->get['pageNo'];
        $pageSize = $this->get['pageSize'];
        $stime    = $this->get['stime'];
        $rankingType = $this->get['rankingType'];//1日 2周 3月
        $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
        $RankAwardAccountObj = new RankAwardAccount();
        $where = " create_time = '{$stime}' and ranking_type = '{$rankingType}'";
        if( $accountId != ""){
            $where .= " and account_id = '{$accountId}'";
        }
        $data = $RankAwardAccountObj->page($pageNo, $pageSize, $where);

        //获取所有的用户id
        $accountIdArr = array_column($data,'account_id');
        $FivepkPlayerInfoModel = new FivepkPlayerInfo();
        $accountObjs = $FivepkPlayerInfoModel->finds($accountIdArr);
        $accountNameArr = array_column($accountObjs,'nick_name','account_id');
        foreach ($data as $key=>$val){
            $data[$key]['nickName'] = $accountNameArr[$val['account_id']];
        }

        $this->setData($data);
        $this->sendJson();
    }
}