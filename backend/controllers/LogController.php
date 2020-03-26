<?php
namespace backend\controllers;

use backend\models\ErrorCode;
use backend\models\MyException;

/**
 * Site controller
 */
class LogController extends MyController
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
            $searchAccount = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";
            $status        = isset( $this->get['status'] ) ? $this->get['status'] : "";
            $stime         = isset( $this->get['stime'] ) ? $this->get['stime'] : "";
            $etime         = isset( $this->get['etime'] ) ? $this->get['etime'] : "";
            if( !empty($searchAccount) )
            {
                $where .= " and admin_id = '{$searchAccount}'";
            }
            if( !empty($status) )
            {
                $where .= " and status = '{$status}'";
            }
            if( !empty($stime) )
            {
                $stime = strtotime($stime." 00:00:00");
                $where .= " and created_at > '{$stime}'";
            }
            if( !empty($etime) )
            {
                $etime = strtotime($etime." 23:59:59");
                $where .= " and created_at <= '{$etime}'";
            }
            $data = $this->Log->page($pageNo, $pageSize, $where);
            $account = $this->Log->accountNum($where);
            $page = array(
                'account' => $account,
                'maxPage' => ceil($account/$pageSize),
                'nowPage' => $pageNo
            );
            if( $page['maxPage'] != 0 && $page['maxPage'] < $page['nowPage'] )
            {
                throw new MyException( ErrorCode::ERROR_PAGE_UNKNOWN );
            }

            //$FunListObj = new FunList();
            //$AccountObj = new Account();
            foreach ($data as $key=>$val)
            {
                $data[$key]['functionInfo'] = $this->FunList->findBase($val['function_list_id']);
                $data[$key]['adminInfo']    = $this->Account->findBase($val['admin_id']);
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
