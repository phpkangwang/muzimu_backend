<?php
namespace backend\controllers;

use backend\models\ErrorCode;
use backend\models\log\AppErrorLog;
use backend\models\MyException;

/**
 * Site controller
 */
class AppErrorLogController extends MyController
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
            $pageNo   = $this->get['pageNo'];
            $pageSize = $this->get['pageSize'];
            $accountId     = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";
            $stime         = isset( $this->get['stime'] )     ? $this->get['stime'] : "";
            $etime         = isset( $this->get['etime'] )     ? $this->get['etime'] : "";

            $where = "  1";
            if( !empty($accountId) )
            {
                $where .= " and account_id = '{$accountId}'";
            }
            if( !empty($stime) )
            {
                $stime = strtotime($stime." 00:00:00");
                $where .= " and create_time > '{$stime}'";
            }
            if( !empty($etime) )
            {
                $etime = strtotime($etime." 23:59:59");
                $where .= " and create_time <= '{$etime}'";
            }
            $AppErrorLogModel = new AppErrorLog();
            $data = $AppErrorLogModel->page($pageNo, $pageSize, $where);
            foreach ($data as $key=>$val)
            {
                $data[$key]['create_time']   = date("Y-m-d H:i:s", $data[$key]['create_time']);
            }
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


}
