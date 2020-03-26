<?php
namespace backend\controllers\finance;

use backend\controllers\MyController;
use common\models\game\FivepkDiamond;
use Yii;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\FivepkSeoidDiamond;

class TreasurerController extends MyController
{
    /**
     * 总代理钻石列表
     * @return string
     */
    public function actionZdlDiamondList()
    {
        try{
            //获取所有的总代理
            $nameArr = ['总代理'];
            $agentRoleObjs = $this->Role->findRoleByName($nameArr);
            $agentRoleIds  = array_column($agentRoleObjs, 'id');
            $AccountObjs   = $this->Account->findByRoleIds($agentRoleIds);
            $PopCodes      = array_column($AccountObjs, 'pop_code');
            $SeoidDiamondObjs = $this->SeoidDiamond->findBySeoids($PopCodes);
            foreach ($AccountObjs as $key=>$AccountObj){
                $AccountObjs[$key]['SeoidDiamond'] = array();
                $AccountObjs[$key]['RoleInfo'] = $this->Role->findBase($AccountObj['role']);
                foreach ($SeoidDiamondObjs as $SeoidDiamondObj){
                    if( $AccountObjs[$key]['pop_code'] == $SeoidDiamondObj['seoid']){
                        $AccountObjs[$key]['SeoidDiamond'] = $SeoidDiamondObj;
                    }

                }
            }
            $this->setData($AccountObjs);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  总代理钻石详情
     * @return string
     */
    public function actionZdlDiamondView()
    {
        try{
            if( !isset( $this->get['id'] ) ){
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $id = $this->get['id'];
            $data = $this->SeoidDiamond->findBase($id);
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 上钻操作
     * @throws \yii\db\Exception
     */
    public function actionDiamondUp()
    {
        try{
            //如果接受钻石的人不存在或者数量不存在
            if( !isset( $this->post['acceptPersonPopCode'] ) || !isset( $this->post['diamondChange'] )){
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $sendPersonPopCode   = isset($this->post['sendPersonPopCode'])?$this->post['sendPersonPopCode'] : "";
            $acceptPersonPopCode = $this->post['acceptPersonPopCode'];
            $diamondChange       = $this->post['diamondChange'];
            $content             = isset( $this->post['content'] ) ? $this->post['content'] : "";
            if( $diamondChange == 0 ){
                throw new MyException( ErrorCode::ERROR_DIAMOND_NUM );
            }
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            $acceptPersonAccount = $this->Account->findByPopCode($acceptPersonPopCode);
            //如果发送钻石的人没有，就必须是管理员或者超级管理员登录的角色才可以
            if( empty( $sendPersonPopCode ) ){
                $changeDiamondAccountId  = $this->Account->getUseParentDiamond($this->loginId);
                $changeDiamondAccountRoleName = $this->Account->getRoleName($changeDiamondAccountId);
                $nameArr = ['超级管理员','管理员'];
                if( !in_array($changeDiamondAccountRoleName, $nameArr) ){
                    throw new MyException( ErrorCode::ERROR_ACCOUNT_FUN_NOT_ACCESS );
                }

                //接受钻石的人的角色必须是总代理
                $nameArr = ['总代理'];
                $agentRoleObjs = $this->Role->findRoleByName($nameArr);
                $agentRoleIds = array_column($agentRoleObjs,'id');
                if( !in_array($acceptPersonAccount['role'], $agentRoleIds) ){
                    throw new MyException( ErrorCode::ERROR_DIAMOND_MUST_AGENT );
                }
            }else{
                //如果不是管理员或者超级管理员就要判断他们是不是上下级关系
                $sendPersonAccount = $this->Account->findByPopCode($sendPersonPopCode);
                //判断推广码是否存在
                if( empty($sendPersonAccount) || empty($acceptPersonAccount) ){
                    throw new MyException( ErrorCode::ERROR_POP_CODE );
                }
                if( !$this->AccountRelation->isSon($sendPersonAccount['id'], $acceptPersonAccount['id']) )
                {
                    throw new MyException( ErrorCode::ERROR_NOT_SON );
                }else{
                    //代理商给下级添加钻石，先减去代理商的钻石
                    $FivepkSeoidDiamondSendObj = FivepkSeoidDiamond::find()->where('seoid = :seoid',array(':seoid'=>$sendPersonPopCode))->one();
                    $FivepkSeoidDiamondSendObjDiamond = empty( $FivepkSeoidDiamondSendObj ) ? 0 : $FivepkSeoidDiamondSendObj->diamond;
                    $FivepkSeoidDiamondSendData = array(
                        'diamond' => $FivepkSeoidDiamondSendObjDiamond - $diamondChange
                    );
                    if( $FivepkSeoidDiamondSendData['diamond'] < 0 ){
                        throw new MyException( ErrorCode::ERROR_DIAMOND_NUM_MINUS );
                    }
                    $FivepkSeoidDiamondSendObj->add($FivepkSeoidDiamondSendData);
                }
            }
            //接受钻石的人添加钻石
            $FivepkSeoidDiamondAcceptObj = FivepkSeoidDiamond::find()->where('seoid = :seoid',array(':seoid'=>$acceptPersonPopCode))->one();
            //假如某个用户从来没有钻石，就是没有数据的时候添加一条数据
            if( empty($FivepkSeoidDiamondAcceptObj) ){
                $FivepkSeoidDiamondAcceptDiamondBefore = 0;
                $FivepkSeoidDiamondAcceptObj = $this->FivepkSeoidDiamond;
            }else{
                $FivepkSeoidDiamondAcceptDiamondBefore = $FivepkSeoidDiamondAcceptObj->diamond;
            }
            $FivepkSeoidDiamondAcceptData = array(
                'diamond' => $FivepkSeoidDiamondAcceptDiamondBefore + $diamondChange,
                'seoid'   => $acceptPersonPopCode
            );
            if( $FivepkSeoidDiamondAcceptData['diamond'] < 0 ){
                throw new MyException( ErrorCode::ERROR_DIAMOND_NUM_MINUS );
            }
            $FivepkSeoidDiamondAcceptObj = $FivepkSeoidDiamondAcceptObj->add($FivepkSeoidDiamondAcceptData);
            $FivepkSeoidDiamondAcceptDiamondAfter = $FivepkSeoidDiamondAcceptObj['diamond'];
            //添加一条钻石修改记录
            $FivepkDiamondData = [
                'diamond_up'     => $diamondChange > 0 ? $diamondChange : 0,
                'diamond_down'   => $diamondChange < 0 ? abs($diamondChange) : 0,
                'diamond_before' => $FivepkSeoidDiamondAcceptDiamondBefore,
                'diamond_after'  => $FivepkSeoidDiamondAcceptDiamondAfter,
                'seoid'          => $FivepkSeoidDiamondAcceptObj['seoid'],
                'operator'       => $this->loginInfo['name'],
                'diamond_type'   => $content,
                'operator_time'  => $this->time*1000
            ];
            $this->FivepkDiamond->add($FivepkDiamondData);
            $tr->commit();
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   上下钻记录
     */
    public function actionDiamondRecordSum()
    {
        $popCode = isset( $this->get['popCode'] ) ? strtoupper($this->get['popCode']) : "";
        $stime      = isset( $this->get['stime'] ) ?  $this->get['stime'] : date('Y-m-d',$this->time);
        $etime      = isset( $this->get['etime'] ) ?  $this->get['etime']." 23:59:59" : date('Y-m-d 23:59:59',$this->time);

        if( empty($popCode) ){
            $parentAccountId = $this->loginId;
        }else{
            $popCodeObj = $this->Account->findByPopCode($popCode);
            $parentAccountId = $popCodeObj['id'];
        }

        $parentAccountId = $this->Account->findCanUsePopCodeAccount($parentAccountId);
        $nameArr = ['总代理','代理商'];
        $needAccountObjs = $this->AccountRelation->findSonInRole($nameArr, $parentAccountId);
        $needAccountObjs = $this->getDiamondRecordSum($needAccountObjs,$stime,$etime);
        $this->setData($needAccountObjs);
        $this->sendJson();
    }

    public function getDiamondRecordSum($needAccountObjs,$stime,$etime)
    {
        $stime = strtotime($stime)*1000;
        $etime = strtotime($etime)*1000;
        foreach ($needAccountObjs as $key=>$accountObj) {
            $where = " seoid = '{$accountObj['pop_code']}' and operator_time > '{$stime}' and operator_time <= '{$etime}'";
            $needAccountObjs[$key]['diamondSum'] = $this->FivepkDiamond->getSum( $where );
            $needAccountObjs[$key]['role'] = $this->Role->findBase( $accountObj['role'] );
        }
        return $needAccountObjs;
    }

    /**
     *   获取某个总代理下面的所有代理商
     */
    public function actionDllDiamondList(){
        try{
            if( !isset( $this->get['zdlAccountId'] ) ){
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $zdlAccountId = $this->get['zdlAccountId'];
//            $nameArr = ['超级管理员','管理员'];
//            $superAdminRoleObjs = $this->Role->findRoleByName($nameArr);
//            $superAdminRoleIds = array_column($superAdminRoleObjs,'id');
//            if( !in_array($this->loginInfo['role'], $superAdminRoleIds) && $zdlAccountId != $this->loginId ){
//                throw new MyException( ErrorCode::ERROR_ACCOUNT_FUN_NOT_ACCESS );
//            }

            //获取这个总代理下面的所有代理商
            $dlsArr  = array();
            $nameArr = ['代理商'];
            $dlsRoleObjs = $this->Role->findRoleByName($nameArr);
            $dlsRoleIds = array_column($dlsRoleObjs,'id');
            //获取总代理下面的所有下级

            $sonIds = $this->AccountRelation->findSon($zdlAccountId, false);
            $sonObjs = $this->Account->finds($sonIds);

            foreach ($sonObjs as $sonObj){
                if( in_array($sonObj['role'], $dlsRoleIds)){
                    array_push($dlsArr, $sonObj);
                }
            }
            $PopCodes = array_column($dlsArr, 'pop_code');
            $SeoidDiamondObjs = $this->SeoidDiamond->findBySeoids($PopCodes);
            foreach ($dlsArr as $key=>$AccountObj){
                $dlsArr[$key]['SeoidDiamond'] = array();
                $dlsArr[$key]['RoleInfo'] = $this->Role->findBase($AccountObj['role']);
                foreach ($SeoidDiamondObjs as $SeoidDiamondObj){
                    if( $dlsArr[$key]['pop_code'] == $SeoidDiamondObj['seoid']){
                        $dlsArr[$key]['SeoidDiamond'] = $SeoidDiamondObj;
                    }
                }
            }
            $this->setData($dlsArr);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 上下钻详情列表
     * @return string
     */
    public function actionDiamondRecord()
    {
        try{
            if( !isset( $this->get['seoid'] ) || !isset( $this->get['pageNo'] ) || !isset( $this->get['pageSize'] ) ){
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $seoid    = $this->get['seoid'];
            $pageNo   = $this->get['pageNo'];
            $pageSize = $this->get['pageSize'];
            $stime    = !empty($this->get['stime'])?strtotime($this->get['stime']." 00:00:00")*1000:strtotime(date('Y-m-d')." 00:00:00")*1000;
            $etime    = !empty($this->get['etime'])?strtotime($this->get['etime']." 23:59:59")*1000:strtotime(date('Y-m-d')." 23:59:59")*1000;;
            $where = " seoid = '{$seoid}' and operator_time > '{$stime}' and operator_time <= '{$etime}'";
            $data = $this->FivepkDiamond->Page( $pageNo, $pageSize, $where);
            $account = $this->FivepkDiamond->Count($where);
            $page = array(
                'account' => $account,
                'maxPage' => ceil($account/$pageSize),
                'nowPage' => $pageNo
            );

            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   获取代理商的钻石数量
     */
    public function actionDiamondByPopcode()
    {
        try{
            if( !isset( $this->get['PopCode'] ) ){
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $PopCode = $this->get['PopCode'];
            //代理商下面的开分员使用的是代理商的钻石
            $AccountObj = $this->Account->findByPopCode($PopCode);
            $useDiamondId = $this->Account->getUseParentDiamond($AccountObj->id);
            $useDiamondObj = $AccountObj = $this->Account->findBase($useDiamondId);
            $useDiamondPopCode = $useDiamondObj['pop_code'];
            $obj = FivepkSeoidDiamond::find()->where('seoid = :seoid',array(':seoid'=>$useDiamondPopCode))->one();
            $data['popCode'] = $useDiamondPopCode;
            if( empty($obj) ){
                $data['diamond'] = 0;
            }else{
                $data['diamond'] = $obj['diamond'];
            }
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   获取代理商的钻石数量
     */
    public function actionDiamondByAccountId()
    {
        try{
            if( !isset( $this->get['accountId'] ) ){
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $accountId = $this->get['accountId'];
            $accountObj = $this->Account->findBase($accountId);
            if( empty($accountObj) ){
                throw new MyException( ErrorCode::ERROR_OBJ );
            }
            $PopCode = $accountObj['pop_code'];
            $obj = FivepkSeoidDiamond::find()->where('seoid = :seoid',array(':seoid'=>$PopCode))->one();
            $data['popCode'] = $PopCode;
            if( empty($obj) ){
                $data['diamond'] = 0;
            }else{
                $data['diamond'] = $obj['diamond'];
            }
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


}

?>