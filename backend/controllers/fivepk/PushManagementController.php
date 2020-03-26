<?php

namespace backend\controllers\fivepk;

use \backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use common\models\PushManagement;
use common\services\JgPush;
use common\services\ToolService;
use Yii;


class PushManagementController extends MyController
{


    //列表
    public function actionPushList()
    {
//        Tool::checkParam(['pageNo', 'pageSize'], $this->get);
//        $pageNo   = $this->get['pageNo'];
//        $pageSize = $this->get['pageSize'];
        $class = new  PushManagement();
        $list  = $class->tableList();

        foreach ($list as &$value) {
            $value['updated_at'] = empty($value['updated_at']) ? '' : date(Tool::DATE_USUALLY_FORMAT, $value['updated_at']);
            $value['created_at'] = date(Tool::DATE_USUALLY_FORMAT, $value['created_at']);
            $value['timing_at']  = date(Tool::DATE_USUALLY_FORMAT, $value['timing_at']);
        }

        $this->setData($list);
        $this->sendJson();
    }


    //添加|修改
    public function actionPushAdd()
    {

        try {
            Tool::checkParam(['title', 'content', 'timingStatus'], $this->post);

            if (!in_array($this->post['timingStatus'], [1, 2])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $data = [
                'title'         => $this->post['title'],
                'content'       => $this->post['content'],
                'timing_at'     => isset($this->post['timingAt']) ? strtotime($this->post['timingAt']) : '',
                'timing_status' => intval($this->post['timingStatus']),
                'admin_id'      => $this->loginInfo['id'],
                'admin_name'    => $this->loginInfo['name'],
                'updated_at'    => 0,
            ];

            $class = new  PushManagement();

            if (Tool::isIssetEmpty($this->post['id'])) {
                $data['created_at'] = $this->time;
                $data['status']     = 1;
            } else {
                $class = $class->findOneByField('id', intval($this->post['id']));
                if ($class->timing_status == 1 || $class->status != 1) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }
                $data['updated_at'] = $this->time;
            }
            if ($data['timing_status'] == 1) {

                $JgPush           = new JgPush();
                $response         = $JgPush->push($data['content'], $data['title']);
                $data['response'] = json_encode($response);
                if (isset($response['http_code']) && $response['http_code'] == 200 && isset($response['body']['msg_id'])) {
                    //成功
                    $data['status'] = 2;
                    $data['msg_id'] = $response['body']['msg_id'];
                } else {
                    $data['status'] = 3;
                }
                $data['timing_at'] = $this->time;

            }

            $return = $class->add($data);

            if (empty($return)) {
                throw new MyException(ErrorCode::ERROR_SYSTEM);
            }


            $return['updated_at'] = empty($return['updated_at']) ? '' : Tool::dateUsually($return['updated_at']);
            $return['created_at'] = Tool::dateUsually($return['created_at']);
            $return['timing_at']  = Tool::dateUsually($return['timing_at']);


            $this->setData($return);

            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //删除
    public function actionPushDelete()
    {
        try {
            Tool::checkParam(['id'], $this->post);
            $class = new  PushManagement();
            $class->deleteAll('id=' . intval($this->post['id']));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


}