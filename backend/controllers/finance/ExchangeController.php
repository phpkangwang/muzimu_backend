<?php

namespace backend\controllers\finance;

use backend\models\Tool;
use common\models\StoreItemExchangeList;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;

class ExchangeController extends MyController
{
    /**
     *   兑换列表
     */
    public function actionExchangeList()
    {
        $data = $this->StoreItemExchangeList->tableList();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *   兑换列表添加修改
     */
    public function actionExchangeListAdd()
    {
        try {
            Tool::checkParam(['maxNum', 'exchangeType', 'awardNum', 'prize'], $this->post);

            $postData = array();
            if (isset($this->post['id'])) {
                $id  = $this->post['id'];
                $obj = StoreItemExchangeList::findOne($id);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_OBJ);
                }
            } else {
                $obj                      = new StoreItemExchangeList();
                $postData['created_time'] = $this->time;
            }
            $postData['prize']         = intval($this->post['prize']);
            $postData['max_num']       = intval($this->post['maxNum']);
            $postData['lost_num']      = intval($this->post['maxNum']);
            $postData['exchange_type'] = intval($this->post['exchangeType']);
            $postData['award_num']     = intval($this->post['awardNum']);
            $postData['updated_time']  = $this->time;
            $data                      = $obj->add($postData);
//            $data = $obj->add($postData);
            $this->setData($data);
            $this->sendJson();
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   删除兑换列表
     */
    public function actionExchangeListDelete()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = $this->get['id'];
            $this->StoreItemExchangeList->del($id);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   兑换列表记录
     */
    public function actionExchangeRecordList()
    {
        Tool::checkParam(['pageNo', 'pageSize', 'stime', 'etime'], $this->get);

        $pageNo       = $this->get['pageNo'];
        $pageSize     = $this->get['pageSize'];
        $stime        = $this->get['stime'];
        $etime        = $this->get['etime'];
        $accountId    = isset($this->get['accountId']) ? $this->get['accountId'] : "";
        $status       = isset($this->get['status']) ? $this->get['status'] : "";
        $exchangeType = isset($this->get['exchangeType']) ? $this->get['exchangeType'] : "";
        $where        = " 1 ";
        if (!empty($accountId)) {
            $where .= " and account_id = '{$accountId}' ";
        }

        if (!empty($status)) {
            $where .= " and status = '{$status}' ";
        }

        if (!empty($exchangeType)) {
            $where .= " and exchange_type = '{$exchangeType}' ";
        }

        if (!empty($stime)) {
            $stime = strtotime($stime . " 00:00:00");
            $where .= " and create_time > '{$stime}' ";
        }

        if (!empty($etime)) {
            $etime = strtotime($etime . " 23:59:59");
            $where .= " and create_time <= '{$etime}' ";
        }
        $data = $this->StoreItemExchangeRecord->page($pageNo, $pageSize, $where);
        foreach ($data as $key => $val) {
            $data[$key]['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
            $data[$key]['update_time'] = date('Y-m-d H:i:s', $val['update_time']);
        }
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *   兑换记录统计列表
     */
    public function actionExchangeRecordListAccount()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize']) || !isset($this->get['stime']) || !isset($this->get['etime'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'];
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";

            $where = " 1 ";
            if (!empty($accountId)) {
                $where .= " and account_id = '{$accountId}' ";
            }

            $data = $this->StoreItemExchangeRecord->pageSum($pageNo, $pageSize, $where);

        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   兑换列表修改状态
     */
    public function actionExchangeRecordUpdateStatus()
    {
        try {
            if (!isset($this->post['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id  = $this->post['id'];
            $obj = $this->StoreItemExchangeRecord->updateStatus($id);
            //发送一封邮件
            $param = 'accountId=' . $obj['account_id'] . "&mailType=1" . "&mailFormat=2" . "&mailMessageStr=" . json_encode(array(
                    'exchangeTime'     => $obj['update_time'] * 1000,
                    'consumeItemCount' => $obj['prize'],
                    'exchangePhone'    => $obj['phone'],
                    'award'            => json_encode(array(
                        Yii::$app->params['itemType']['话费'] => $obj['award_num']))
                ), JSON_FORCE_OBJECT);
            /**
             *   'award' => json_encode(
             * array(
             * Yii::$app->params['itemType']['金币'] => $obj['award_num']
             * ),JSON_FORCE_OBJECT
             * ),
             */
            $this->remoteInterface->sendMail($param);
            $this->setData($obj);
            $this->sendJson();
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


}

?>