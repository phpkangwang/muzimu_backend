<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-28
 * Time: 10:11
 */

namespace backend\controllers\fivepk;


use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;
use common\models\game\DataDictionaryConfiguration;
use common\models\game\DataDictionaryConfigurationDetails;
use common\models\game\DataDictionaryConfigurationDetials;
use common\models\game\DataFivepkNewer;
use common\models\game\DataTypeCode;
use backend\models\Tool;

class ConfigurationController extends MyController
{
    /**
     *  获取所有的模块类型下拉列表
     */
    public function actionGetModuleKeyType()
    {
        $type1 = "module_code";
        $data1 = $this->DataTypeCode->GetByType($type1);
        $type2 = "key_type_code";
        $data2 = $this->DataTypeCode->GetByType($type2);
        $type3 = "key_code";
        $data3 = $this->DataTypeCode->GetByType($type3);
        $this->setData(array('module'=>$data1,'KeyType'=>$data2,'keyCode'=>$data3));
        $this->sendJson();
    }

    /**
     *   配置列表
     */
    public function actionConfig()
    {
        try {
            $type       = isset( $this->get['type'] ) ? $this->get['type'] : "";
            $typeCode   = isset( $this->get['typeCode'] ) ? $this->get['typeCode'] : "";
            $typeName   = isset( $this->get['typeName'] ) ? $this->get['typeName'] : "";
            $createUser = isset( $this->get['createUser'] ) ? $this->get['createUser'] : "";

            $data = $this->DataTypeCode->tableList();
            foreach ($data as $key => $val)
            {
                if( $type != "" && (strpos($val['type'], $type) === false) ){
                    unset($data[$key]);
                    continue;
                }
                if( $typeCode != "" && (strpos($val['type_code'], $typeCode) === false) ){
                    unset($data[$key]);
                    continue;
                }
                if( $typeName != "" && (strpos($val['type_name'], $typeName) === false) ){
                    unset($data[$key]);
                    continue;
                }
                if( $createUser != "" && (strpos($val['operator'], $createUser) === false) ){
                    unset($data[$key]);
                    continue;
                }
                $data[$key]['create_time'] = date("Y-m-d H:i:s",$data[$key]['create_time']/1000);
            }
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   配置列表--增加修改
     */
    public function actionConfigAdd()
    {
        try {
            if ( !isset($this->post['type']) || !isset($this->post['typeCode']) || !isset($this->post['typeName']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $id        = isset( $this->post['id'] ) ? $this->post['id'] : "";
            $type      = $this->post['type'];
            $typeCode  = $this->post['typeCode'];
            $typeName  = $this->post['typeName'];
            $postData = array(
                'type'        => $type,
                'type_code'   => $typeCode,
                'type_name'   => $typeName,
                'operator' => $this->loginInfo['name']
            );
            if( !empty($id) )
            {
                //修改
                $obj = DataTypeCode::findOne($id);
                $obj->add($postData);
            }else{
                //新增
                $postData['create_time'] = $this->time*1000;
                $this->DataTypeCode->add($postData);
            }
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   功能列表
     */
    public function actionDictionary()
    {
        try {
            $moduleCode  = isset( $this->get['moduleCode'] ) ? $this->get['moduleCode'] : "";
            $keyTypeCode = isset( $this->get['keyTypeCode'] ) ? $this->get['keyTypeCode'] : "";
            $active      = isset( $this->get['active'] ) ? $this->get['active'] : "";

            $data = $this->DataDictionaryConfiguration->tableList();
            foreach ($data as $key => $val)
            {
                if( $moduleCode != "" && $val['module_code'] != $moduleCode ){
                    unset($data[$key]);
                    continue;
                }
                if( $keyTypeCode != "" && $val['key_type_code'] != $keyTypeCode ){
                    unset($data[$key]);
                    continue;
                }
                if( $active != "" && $val['active'] != $active ){
                    unset($data[$key]);
                    continue;
                }
                $data[$key]['update_time'] = date("Y-m-d H:i:s",$data[$key]['update_time']/1000);
                $data[$key]['create_time'] = date("Y-m-d H:i:s",$data[$key]['create_time']/1000);
            }
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   功能列表--增加修改
     */
    public function actionDictionaryAdd()
    {
        try {
            if (!isset($this->post['moduleCode']) || !isset($this->post['moduleName'])
                || !isset($this->post['keyTypeCode']) || !isset($this->post['keyTypeName'])
                || !isset($this->post['orderNum']) || !isset($this->post['discription'])
                || !isset($this->post['note']) || !isset($this->post['active'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = isset($this->post['id']) ? $this->post['id'] : "";
            $moduleCode = $this->post['moduleCode'];
            $moduleName = $this->post['moduleName'];
            $keyTypeCode = $this->post['keyTypeCode'];
            $keyTypeName = $this->post['keyTypeName'];
            $orderNum = $this->post['orderNum'];
            $discription = $this->post['discription'];
            $note = $this->post['note'];
            $active = $this->post['active'];

            $postData = array(
                'module_code' => $moduleCode,
                'module_name' => $moduleName,
                'key_type_code' => $keyTypeCode,
                'key_type_name' => $keyTypeName,
                'order_num' => $orderNum,
                'discription' => $discription,
                'note' => $note,
                'active' => $active,
                'update_time' => $this->time * 1000
            );
            if (!empty($id)) {
                //修改
                $obj = DataDictionaryConfiguration::findOne($id);
                $obj->add($postData);
            } else {
                //新增
                $postData['create_time'] = $this->time * 1000;
                $this->DataDictionaryConfiguration->add($postData);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   功能列表详情
     */
    public function actionDictionaryDetail()
    {
        try {
            $keyTypeCode = isset($this->get['keyTypeCode']) ? $this->get['keyTypeCode'] : "";
            $keyCode = isset($this->get['keyCode']) ? $this->get['keyCode'] : "";
            $active = isset($this->get['active']) ? $this->get['active'] : "";

            $data = $this->DataDictionaryConfigurationDetails->tableList();
            foreach ($data as $key => $val) {
                if ($keyTypeCode != "" && $val['key_type_code'] != $keyTypeCode) {
                    unset($data[$key]);
                    continue;
                }
                if ($keyCode != "" && $val['key_code'] != $keyCode) {
                    unset($data[$key]);
                    continue;
                }
                if ($active != "" && $val['active'] != $active) {
                    unset($data[$key]);
                    continue;
                }
                $data[$key]['update_time'] = date("Y-m-d H:i:s", $data[$key]['update_time'] / 1000);
                $data[$key]['create_time'] = date("Y-m-d H:i:s", $data[$key]['create_time'] / 1000);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   配置列表--功能列表详情修改
     */
    public function actionDictionaryDetailAdd()
    {
        try {
            if (!isset($this->post['keyTypeCode']) || !isset($this->post['parentKeyCode'])
                || !isset($this->post['keyCode']) || !isset($this->post['keyName'])
                || !isset($this->post['valueCodeInt'])
                || !isset($this->post['valueCodeDecimal']) || !isset($this->post['orderNum'])
                || !isset($this->post['discription']) || !isset($this->post['active'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = isset($this->post['id']) ? $this->post['id'] : "";
            $keyTypeCode = $this->post['keyTypeCode'];
            $parentKeyCode = $this->post['parentKeyCode'];
            $keyCode = $this->post['keyCode'];
            $keyName = $this->post['keyName'];
            $valueCodeInt = $this->post['valueCodeInt'];
            $valueCodeVarchar = $this->post['value_code_varchar'];
            $valueCodeDecimal = $this->post['valueCodeDecimal'];
            $orderNum = $this->post['orderNum'];
            $discription = $this->post['discription'];
            $active = $this->post['active'];

            $postData = array(
                'key_type_code' => $keyTypeCode,
                'parent_key_code' => $parentKeyCode,
                'key_code' => $keyCode,
                'key_name' => $keyName,
                'value_code_int' => $valueCodeInt,
                'value_code_varchar' => $valueCodeVarchar,
                'value_code_decimal' => $valueCodeDecimal,
                'order_num' => $orderNum,
                'discription' => $discription,
                'active' => $active,
                'update_time' => $this->time * 1000
            );
            if (!empty($id)) {
                //修改
                $obj = DataDictionaryConfigurationDetails::findOne($id);
                $obj->add($postData);
            } else {
                //新增
                $postData['create_time'] = $this->time * 1000;
                $this->DataDictionaryConfigurationDetails->add($postData);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   翻译配置--查看列表
     */
    public function actionTranslationConfigList()
    {
        try {
            $db = new \common\models\game\TranslationConfig;
            $data = $db->tableList();
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            throw new MyException(ErrorCode::ERROR_SYSTEM);
        }
    }

    /**
     *   翻译配置--添加/修改
     */
    public function actionTranslationConfigAdd()
    {
        try {
            if (
                Tool::isIssetEmpty($this->post['title'],false)//名字
                || Tool::isIssetEmpty($this->post['translation'],false)//翻译
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $id = intval(Tool::examineEmpty($this->post['id'], 0));
            $updateData = array(
                'updated_at' => $this->time,//修改时间
                'operator' => $this->loginInfo['name'],//登陆名
                'title' => $this->post['title'],//名字
                'translation' => $this->post['translation'],//翻译
            );

            if ($id > 0) {
                //修改
                $updateData['id'] = $id;
                $obj = $this->TranslationConfig->findOne($updateData['id']);
                $getData = $obj->add($updateData);
            } else {
                //新增
                $updateData['created_at'] = $this->time;
                $getData = $this->TranslationConfig->add($updateData);
            }
            $getData['created_at'] = date('Y-m-d H:i:s', $getData['created_at']);
            $getData['updated_at'] = date('Y-m-d H:i:s', $getData['updated_at']);
            $this->setData($getData);
            $this->sendJson();
        } catch (MyException $e) {
            throw new MyException(ErrorCode::ERROR_SYSTEM);
        }

    }

    /**
     *   翻译配置--删除
     */
    public function actionTranslationConfigDel()
    {

        try {
            if (Tool::isIssetEmpty($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = intval($this->get['id']);
            $this->TranslationConfig->del($id);
            $this->sendJson();
        } catch (MyException $e) {
            throw new MyException(ErrorCode::ERROR_SYSTEM);
        }

    }

}