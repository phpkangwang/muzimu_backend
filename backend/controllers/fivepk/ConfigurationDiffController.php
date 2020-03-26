<?php
namespace backend\controllers\fivepk;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;
use common\models\game\DataDiffDictionaryConfiguration as DataDictionaryConfiguration;
use common\models\game\DataDiffDictionaryConfigurationDetails as DataDictionaryConfigurationDetails;
use common\models\game\DataDiffTypeCode as DataTypeCode;
use backend\models\Tool;

class ConfigurationDiffController extends MyController
{
    /**
     * @默认config_type
     */
    const CONFIG_TYPE = 1;

    /**
     * @默认from_id
     */
    const FROM_ID = 0;

    /**
     *  获取所有的模块类型下拉列表
     */
    public function actionGetModuleKeyType()
    {
        $type = ['module_code' => 'module', 'key_type_code' => 'KeyType', 'key_code' => 'keyCode'];
        $data = [];
        $DataTypeCode = new DataTypeCode;
        foreach ($type as $key => $value) {
            $data[$value] = $DataTypeCode->GetByType($key);
        }
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *   模块/配置号 列表
     */
    public function actionConfig()
    {
        try {

            $active = '';
            if (isset($this->get['active']) && is_numeric($this->get['active'])) {
                $active = intval($this->get['active']);
            }

            $where = array(
                'type' => Tool::examineEmpty($this->get['type']),//只有module_code和key_code
                'type_code' => Tool::examineEmpty($this->get['typeCode']),
                'type_name' => Tool::examineEmpty($this->get['typeName']),
                'operator' => Tool::examineEmpty($this->get['createUser']),
                'active' => $active,//是否可用
                'config_type' => Tool::examineEmpty($this->get['configType']),//平台 type为 module_code平台无效
                'from_id' => Tool::examineEmpty($this->get['fromId']),//当type为key_code from不为空时候为查询配置号
            );
            $option['likeArr']=[
//                'type_code',
            ];

            $DataTypeCode = new DataTypeCode();
            $data = $DataTypeCode->tableList($where,$option);
//            varDump($data);
            foreach ($data as $key => &$configVal) {
                $configVal['create_time'] = date("Y-m-d H:i:s", $configVal['create_time'] / 1000);
                $configVal['update_time'] = date("Y-m-d H:i:s", $configVal['update_time'] / 1000);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   模块/配置号 增加/修改
     */
    public function actionConfigAdd()
    {
        try {
            if (
                Tool::isIssetEmpty($this->post['type'])
                || Tool::isIssetEmpty($this->post['typeCode'])
                || Tool::isIssetEmpty($this->post['typeName'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = intval(Tool::examineEmpty($this->post['id'], 0));
            $postData = array(
                'type' => $this->post['type'],
                'type_code' => $this->post['typeCode'],
                'type_name' => $this->post['typeName'],
                'operator' => $this->loginInfo['name'],
                'update_time' => $this->time * 1000,
                'discription' => Tool::examineEmpty($this->post['discription']),
                'config_type' => Tool::examineEmpty($this->post['configType'], self::CONFIG_TYPE),
                'active' => intval(Tool::examineEmpty($this->post['active'], 1)),
                'from_id' => Tool::examineEmpty($this->post['fromId']),
            );
            $DataTypeCode = new DataTypeCode();
            if ($id > 0) {
                //修改
                $DataTypeCode = $DataTypeCode::findOne($id);
            } else {
                //新增
//                $postData['operator']=$this->loginInfo['name'];
                $postData['create_time'] = $this->time * 1000;
            }
            $getData = $DataTypeCode->add($postData);
            $this->setData($getData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //---------------------------------------------

    /**
     *   类型列表
     */
    public function actionDictionary()
    {
        try {
            $DataDictionaryConfiguration = new DataDictionaryConfiguration;
            $where = array(
                'module_code' => Tool::examineEmpty($this->get['moduleCode']),
                'module_name' => Tool::examineEmpty($this->get['moduleName']),
                'key_type_name' => Tool::examineEmpty($this->get['keyTypeName']),
                'key_type_code' => Tool::examineEmpty($this->get['keyTypeCode']),
//                'active' => Tool::examineEmpty($this->get['active']),
                'config_type' => Tool::examineEmpty($this->get['configType'], self::CONFIG_TYPE),
//                'from_id' => Tool::examineEmpty($this->get['fromId'], self::FROM_ID),
            );

            $data = $DataDictionaryConfiguration->tableList($where);
            foreach ($data as $key => &$val) {
                $val['update_time'] = date("Y-m-d H:i:s", $data[$key]['update_time'] / 1000);
                $val['create_time'] = date("Y-m-d H:i:s", $data[$key]['create_time'] / 1000);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   类型列表 增加/修改
     */
    public function actionDictionaryAdd()
    {
        try {
            if (
                !isset($this->post['moduleCode'])
                || !isset($this->post['moduleName'])
                || !isset($this->post['keyTypeCode'])
                || !isset($this->post['keyTypeName'])
//                || !isset($this->post['fromId'])
//                || !isset($this->post['orderNum'])
//                || !isset($this->post['discription'])
//                || !isset($this->post['note'])
//                || !isset($this->post['active'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = isset($this->post['id']) ? $this->post['id'] : "";
            $postData = array(
                'module_code' => $this->post['moduleCode'],
                'module_name' => $this->post['moduleName'],
                'key_type_code' => $this->post['keyTypeCode'],
                'key_type_name' => $this->post['keyTypeName'],
//                'order_num' => $this->post['orderNum'],
                'discription' => Tool::examineEmpty($this->post['discription']),
//                'note' => $this->post['note'],
                'active' => intval($this->post['active']),
                'operator' => $this->loginInfo['name'],//创建人
                'update_time' => $this->time * 1000,
                'config_type' => Tool::examineEmpty($this->post['configType'], self::CONFIG_TYPE),
//                'from_id' => Tool::examineEmpty($this->post['fromId'], self::FROM_ID),
            );

            $DataDictionaryConfiguration = new DataDictionaryConfiguration();
            if (!empty($id)) {
                //修改
                $DataDictionaryConfiguration = $DataDictionaryConfiguration::findOne($id);
            } else {
                //新增
                $postData['create_time'] = $this->time * 1000;
            }
            $getData = $DataDictionaryConfiguration->add($postData);

            $this->setData($getData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //---------------------------------------------

    /**
     *   功能列表
     */
    public function actionDictionaryDetail()
    {
        try {
            $active = '';
            if (isset($this->get['active']) && is_numeric($this->get['active'])) {
                $active = intval($this->get['active']);
            }

            $where = array(
                'key_type_code' => Tool::examineEmpty($this->get['keyTypeCode']),//所属
                'parent_key_code' => Tool::examineEmpty($this->get['parentKeyCode']),//父级配置
                'key_name' => Tool::examineEmpty($this->get['keyName']),//
                'key_code' => Tool::examineEmpty($this->get['keyCode']),
                'active' => $active,
                'operator' => Tool::examineEmpty($this->get['createUser']),
//                'config_type' => Tool::examineEmpty($this->get['configType'], self::CONFIG_TYPE),
//                'from_id' => Tool::examineEmpty($this->get['fromId'], self::FROM_ID),
            );

            $DataDictionaryConfigurationDetails = new DataDictionaryConfigurationDetails();
            $data = $DataDictionaryConfigurationDetails->tableList($where);
            foreach ($data as $key => &$val) {
                $val['update_time'] = date("Y-m-d H:i:s", $data[$key]['update_time'] / 1000);
                $val['create_time'] = date("Y-m-d H:i:s", $data[$key]['create_time'] / 1000);
            }

            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   功能列表 增加/修改
     */
    public function actionDictionaryDetailAdd()
    {
        try {

            if (
                !isset($this->post['keyTypeCode'])
//                || !isset($this->post['parentKeyCode'])
                || !isset($this->post['keyCode'])
                || !isset($this->post['keyName'])
                || !isset($this->post['valueCodeInt'])
                || !isset($this->post['valueCodeDecimal'])
//                || !isset($this->post['orderNum'])
                || !isset($this->post['discription'])
                || !isset($this->post['active'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = isset($this->post['id']) ? $this->post['id'] : "";

            $postData = array(
                'key_type_code' => $this->post['keyTypeCode'],//所属
                'parent_key_code' => Tool::examineEmpty($this->post['parentKeyCode']),//父级配置
                'key_code' => $this->post['keyCode'],//配置号
                'key_name' => $this->post['keyName'],//配置名
                'value_code_int' => $this->post['valueCodeInt'],//整型
                'value_code_varchar' => $this->post['value_code_varchar'],//字符
                'value_code_decimal' => $this->post['valueCodeDecimal'],//小数
//                'order_num' => $this->post['orderNum'],
                'discription' => $this->post['discription'],//简介
                'active' => intval($this->post['active']),//是否可用
                'operator' => $this->loginInfo['name'],//创建人
                'update_time' => $this->time * 1000,
//                'config_type' => Tool::examineEmpty($this->post['configType'], self::CONFIG_TYPE),
//                'from_id' => Tool::examineEmpty($this->post['fromId'], self::FROM_ID),
            );

            $DataDictionaryConfigurationDetails = new DataDictionaryConfigurationDetails;

            if (!empty($id)) {
                //修改
                $DataDictionaryConfigurationDetails = $DataDictionaryConfigurationDetails::findOne($id);
            } else {
                //新增
//                $postData['operator']=$this->loginInfo['name'];
                $postData['create_time'] = $this->time * 1000;
            }

            $getData = $DataDictionaryConfigurationDetails->add($postData);
            $this->setData($getData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


}