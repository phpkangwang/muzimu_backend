<?php
namespace backend\controllers\globalrecord;

use backend\controllers\MyController;
use backend\models\globalrecord\GlobalRecordType;
use Yii;
use backend\models\ErrorCode;
use backend\models\MyException;

class GlobalRecordController extends MyController
{

    /**
     *  配置列表添加
     */
    public function actionGlobalTypeAdd()
    {
        try{
            if( !isset( $this->post['name'] ) || !isset( $this->post['content'] ) || !isset( $this->post['status'] ))
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $name     = $this->post['name'];
            $content  = $this->post['content'];
            $status   = $this->post['status'];
            $operator = $this->loginInfo['name'];
            $updated_at = $this->time;

            $data = array(
                'name'      => $name,
                'content'   => $content,
                'status'    => $status,
                'operator'  => $operator,
                'updated_at'=> $updated_at,
            );

            $data['created_at'] = $this->time;
            $Obj = new GlobalRecordType();
            $Obj = $Obj->add($data);

            $this->setData($Obj);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  分页
     *  pageNo  第几页
     * pageSize 一页多少条数据
     */
    public function actionGlobalTypePage()
    {
        try{
            if( !isset( $this->get['pageNo'] ) || !isset( $this->get['pageSize'] )  )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $pageNo   = $this->get['pageNo'];
            $pageSize = $this->get['pageSize'];
            $data = $this->GlobalRecordType->page($pageNo, $pageSize);
            $account = $this->GlobalRecordType->accountNum();
            $page = array(
                'account' => $account,
                'maxPage' => ceil($account/$pageSize),
                'nowPage' => $pageNo
            );

            foreach ($data as $key=>$val)
            {
                $data[$key]['created_at'] = date("Y-m-d H:i:s", $data[$key]['created_at']);
                $data[$key]['updated_at'] = date("Y-m-d H:i:s", $data[$key]['updated_at']);
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }



}

?>