<?php
namespace backend\controllers\dls;

use backend\controllers\MyController;
use backend\models\Account;
use common\services\IpArea;
use Yii;
use backend\models\rbac\Role;
use backend\models\ErrorCode;
use backend\models\MyException;

class SiteController extends MyController
{


    /**
 *   添加登录日志
 */
    public function actionLogLoginAdd()
    {
        try{
            $logLoginData = $this->post;
            $IpArea = new \common\services\IpArea();
            $logLoginData['address'] = $IpArea->getLoginIpAddress($logLoginData['ip']);
            unset($logLoginData['sign']);
            if(empty($logLoginData)){
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $this->LogLogin->add($logLoginData);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   代理商添加登录日志
     */
    public function actionUpdateToken()
    {
        try{
            if( !isset( $this->post['accountId'] ) ||  !isset( $this->post['token'] ) ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId = $this->post['accountId'];
            $token = $this->post['token'];
            $accountObj = Account::findOne($accountId);
            $data = array(
                'token' => $token
            );
            $accountObj->updateAccount($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   代理商添加登录错误次数
     */
    public function actionUpdateErrorLogin()
    {
        try{
            if( !isset( $this->post['accountId'] ) ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId = $this->post['accountId'];
            $error_login_times = $this->post['error_login_times'];
            $accountObj = Account::findOne($accountId);
            $data = array(
                'error_login_times' => $error_login_times
            );
            $accountObj->updateAccount($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   添加用户行为日志
     */
    public function actionLogAdd()
    {
        try{
            if( !isset( $this->post['function_list_id'] ) || !isset( $this->post['status'] ) ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $function_list_id = $this->post['function_list_id'];
            $status = $this->post['status'];
            $logData = array(
                'function_list_id' => $function_list_id,
                'admin_id'   => $this->loginId,
                'status'     => $status,
                'created_at' => $this->time,
            );
            $this->Log->add($logData);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  处理代理商调用的远程接口
     */
    public function actionDlsRemoteInterface(){
        $url = Yii::$app->params['url'].$this->post['url'];
        Yii::info($url,'msg');
        $rs = $this->remoteInterface->doUrl($url);
        $this->setData($rs);
        $this->sendJson();
    }

}

?>