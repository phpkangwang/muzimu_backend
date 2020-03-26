<?php

namespace common\models\pay;

use backend\models\ErrorCode;
use backend\models\Factory;
use backend\models\MyException;
use backend\models\Tool;
use common\models\game\FivepkPoint;
use Yii;

/**
 * This is the model class for table "CpayRecord".
 *
 */
class CpayRecord extends \backend\models\BaseModel
{
    const STATUS_AWAIT = 1;//处理中
    const STATUS_DONE = 3;//完成
    const STATUS_RETURN = 4;//返还
    const STATUS_IS_BANK = 100;//表示这条数据是绑定银行卡

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cpay_record';
    }

    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'update_time'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'account_id'      => '用户id',
            'nick_name'       => '昵称',
            'name'            => '账户',
            'seoid'           => '推广号',
            'withdraw_before' => '转移前钻石',
            'withdraw'        => '转移钻石',
            'withdraw_after'  => '转移后钻石',
            'operator_id'     => '操作人',
            'status'          => '状态',
            'update_time'     => '修改时间',
            'create_time'     => '创建时间',
        ];
    }


    /**
     *  转出
     */
    public function updateStatus($id, $loginInfo)
    {

        $obj = $this->findOneByField('id', $id, true);

        $return = '';
        if ($obj->status != 1 && $obj->withdraw > 0) {
            return $return;
        }

        //获取能够使用最上级的钻石  例如代理商能使用总代理的钻石，代理商给玩家开洗分用的是总代理的钻石
        $AccountObj    = $this->Account->findByPopCode($obj->seoid);
        $useDiamondId  = $this->Account->getUseParentDiamond($AccountObj->id);
        $useDiamondObj = $AccountObj = $this->Account->findBase($useDiamondId);
        $sendPopCode   = $useDiamondObj['pop_code'];

        //开启事务
        $tr  = Yii::$app->game_db->beginTransaction();
        $sql = "update fivepk_seoid_diamond SET diamond=diamond+{$obj->withdraw} WHERE seoid='{$sendPopCode}';";
        Yii::$app->game_db->createCommand($sql)->execute();
        $postData     = array(
            'id'        => $id,
            'status'    => self::STATUS_DONE,
            'accountId' => $obj->account_id
        );
        $intfaceClass = new \backend\models\remoteInterface\remoteInterface();
        //修改状态 主要是清除缓存
        $intfaceClass->cpayStatus($postData);

        $FivepkPoint = new FivepkPoint();

        $upStatus = $FivepkPoint->upStatus($obj->point_id, $FivepkPoint::OPERATOR_TYPE_TRUE, $loginInfo['name']);

        if (!$upStatus) {
            return false;
        }

        $tr->commit();
        $data   = array(
            'operator_id' => $loginInfo['id'],
        );
        $return = $obj->add($data);
        if (isset($return['status']) && $return['status'] == 3) {
            return $return;
        }

        return $return;
    }


    /**
     *  返还给用户
     */
    public function returnUser($operateName, $operatorId)
    {
        try {

            if ($this->status != 1) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            //给用户加钱
            $postData = array(
                'sendPopCode'  => $this->seoid,
                'type'         => 2,//不做记录 不扣代理商的钻
                'acceptUserId' => $this->account_id,
                'num'          => $this->withdraw,
                'operateName'  => $operateName,
                'operatorType' => FivepkPoint::OPERATOR_TYPE_USER
            );

            $intfaceClass = new \backend\models\remoteInterface\remoteInterface();
            //修改状态 主要是清除缓存
            $intfaceClass->cpayStatus(array(
                'id'        => $this->id,
                'status'    => self::STATUS_RETURN,
                'accountId' => $this->account_id
            ));

            $FivepkPoint = new FivepkPoint();

            $FivepkPoint->upStatus($this->point_id, $FivepkPoint::OPERATOR_TYPE_RETURN, $operateName);

            $this->operator_id = $operatorId;

            if ($this->validate() && $this->save()) {
                Factory::RecordController()->UserDiamondUpdate($postData);
                return true;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


}
