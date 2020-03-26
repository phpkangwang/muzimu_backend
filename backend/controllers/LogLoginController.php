<?php
namespace backend\controllers;

use backend\models\ErrorCode;
use backend\models\MyException;

/**
 * Site controller
 */
class LogLoginController extends MyController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }


    /**
     *  分页获取后台操作日志
     */
    public function actionPage()
    {
        try{
            if( !isset( $this->get['pageNo'] ) || !isset( $this->get['pageSize'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            //$obj = new Log();
            $pageNo   = $this->get['pageNo'];
            $pageSize = $this->get['pageSize'];
            $where = "  1";
            $accountId = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";
            $ip        = isset( $this->get['ip'] ) ? $this->get['ip'] : "";
            $address   = isset( $this->get['address'] ) ? $this->get['address'] : "";
            $device    = isset( $this->get['device'] ) ? $this->get['device'] : "";
            $stime     = isset( $this->get['stime'] ) ? $this->get['stime'] : "";
            $etime     = isset( $this->get['etime'] ) ? $this->get['etime'] : "";
            if( !empty($accountId) )
            {
                $where .= " and account_id = '{$accountId}'";
            }
            if( !empty($ip) )
            {
                $where .= " and ip = '{$ip}'";
            }
            if( !empty($address) )
            {
                $where .= " and address = '{$address}'";
            }
            if( !empty($device) )
            {
                if($device == 1)
                {
                    $where .= " and device = 1";
                }else{
                    $where .= " and device != 1";
                }
            }
            if( !empty($stime) )
            {
                $time = strtotime($stime." 00:00:00");
                $where .= " and created_at > '{$time}'";
            }
            if( !empty($etime) )
            {
                $etime = strtotime($etime." 23:59:59");
                $where .= " and created_at <= '{$etime}'";
            }
            $data = $this->LogLogin->page($pageNo, $pageSize, $where);
            $account = $this->LogLogin->accountNum($where);
            $page = array(
                'account' => $account,
                'maxPage' => ceil($account/$pageSize),
                'nowPage' => $pageNo
            );
            if( $page['maxPage'] != 0 && $page['maxPage'] < $page['nowPage'] )
            {
                throw new MyException( ErrorCode::ERROR_PAGE_UNKNOWN );
            }

            foreach ($data as $key=>$val)
            {
                $data[$key]['accountInfo']    = $this->Account->findBase($val['account_id']);
                $data[$key]['created_at']   = date("Y-m-d H:i:s", $data[$key]['created_at']);
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


}
