<?php
namespace common\models\game\big_plate;

use backend\models\Tool;
use common\models\OddsChangePath;
use Yii;

//压住分buff值和触顶值
class DzbOddsCount extends Dzb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'odds_dzb_count';
    }

    /**
     * @return \yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'is_default' => '是否是默认机率',
            'odds_type' => '类型',
            'odds_type_id' => '类型id',
            'bet_score' => '押注分',
            'prefab_five_of_a_kind_count' => '五梅累计值',
            'five_of_a_kind_total_count' => '五梅触顶值',
            'prefab_royal_flush_count' => '同花大顺累计值',
            'royal_flush_total_count' => '同花大顺触顶值',
            'prefab_straight_flush_count' => '同花小顺累计值',
            'straight_flush_total_count' => '同花小顺触顶值',
            'prefab_four_of_a_kind_T_T_count' => '四梅累计值',
            'prefab_four_of_a_kind_max_count' => '四梅触顶值'
        ];
    }

    public function add($data)
    {
        try {
            //修改这个值必须 记录 修改的值
            $arr = Tool::distinctArr($data, $this->attributes, self::attributeLabels()  );
            if (!empty($arr)) {
                if( $this->odds_type == 2){
                    //获取这个机台的名字
                    $machienModel = $this->getModelMachine();
                    $machineObj   = $machienModel::findOne($this->odds_type_id);
                    $OddsChangePathModel = new OddsChangePath();
                    $postData            = array(
                        'game_type' => $this->gameType,
                        'type'      => $OddsChangePathModel->typeMachine,
                        'type_id'   => isset( $machineObj->seo_machine_id ) ? $machineObj->seo_machine_id : "",
                        'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
                    );
                    $OddsChangePathModel->add($postData);
                }

                foreach ($data as $key => $val) {
                    $this->$key = $val;
                }
                if ($this->save()) {
                    return $this->attributes;
                } else {
                    throw new MyException(implode(",", $this->getFirstErrors()));
                }
            }
            return true;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 按照条件查询数据
     * @param $postData
     * @return mixed
     */
    public function tableList($postData)
    {
        $tool = new Tool();
        $rs = array();
        $isDefault = $postData['isDefault'];
        $oddsType  = $postData['oddsType'];
        $oddsTypeId  = $postData['oddsTypeId'];
        $oddsTypeIdArr = explode(",",$oddsTypeId);
        $data = self::find()
            ->where(['is_default'=>$isDefault])
            ->andFilterWhere(['odds_type'=>$oddsType])
            ->andFilterWhere(['odds_type_id'=>$oddsTypeIdArr[0]])
            ->asArray()
            ->all();
        foreach ($data as $key =>$val){
            $rs[$val['bet_score']] = $tool->clearFloatZero($val);
        }
        return $rs;
    }

    /**
     * 按照条件查询数据
     * @param $postWhere
     * @param $postData
     * @return mixed
     */
    public function tableUpdate($postWhere,$postData)
    {
        $oddsTypeId  = $postWhere['oddsTypeId'];
        $oddsTypeIdArr = explode(",",$oddsTypeId);
        $isDefault = $postWhere['isDefault'];
        $oddsType  = $postWhere['oddsType'];
        $betScore  = $postWhere['bet_score'];
        if($oddsType == 2 && $isDefault == 2){
            foreach ($oddsTypeIdArr as $oddsTypeId){
                $obj = self::find()
                    ->where(['odds_type'=>$oddsType])
                    ->andWhere(['bet_score'=>$betScore])
                    ->andWhere(['odds_type_id'=>$oddsTypeId])
                    ->one();
                $obj->add($postData);
            }
        }else{
            return self::updateAll($postData,['and',['is_default'=>$isDefault],['odds_type'=>$oddsType],['bet_score'=>$betScore],['in','odds_type_id',$oddsTypeIdArr]]);
        }
    }

    /**
     * 默认触顶值列表
     * @param $oddsType
     * @return array|\yii\db\ActiveRecord[]
     */
    public function  DefaultOddsCountTopList($oddsType){
        $tool = new Tool();
        $rs = array();
        $data = self::find()->where('is_default = 1 and odds_type = :oddsType',array(':oddsType'=>$oddsType))->asArray()->all();
        foreach ($data as $key =>$val){
            $rs[$val['bet_score']] = $tool->clearFloatZero($val);
        }
        return $rs;
    }

    /**
     * 用户触顶值
     * @param $oddsType
     * @param $oddsTypeId
     * @return array
     */
    public function  OddsCountTopList($oddsType,$oddsTypeId){
        $tool = new Tool();
        $rs = array();
        $data = self::find()->where('odds_type = :oddsType and odds_type_id = :odds_type_id',
            array(':oddsType'=>$oddsType, ':odds_type_id'=>$oddsTypeId))->asArray()->all();
        foreach ($data as $key =>$val){
            unset($val['id'],$val['is_default'],$val['odds_type'],$val['odds_type_id']);
            $rs[$val['bet_score']] = $tool->clearFloatZero($val);
        }
        return $rs;
    }

    /**
     * 修改 这个押注分下的所有的 触顶值
     * @param $postData
     * @param $whereBy  修改的条件
     * @return bool
     */
    public function OddsCountTopUpdate($whereBy, $postData)
    {
        //把需要修改的数据放进一个数组里面去
        $updateArr = array();
        foreach ( $postData as $key=>$val)
        {
            //五梅触顶值
            if( $key == "five_of_a_kind_total_count"){
                $updateArr[$key] = $val;
            }
            //同花大顺触顶值
            if( $key == "royal_flush_total_count"){
                $updateArr[$key] = $val;
            }
            //同花小顺触顶值
            if( $key == "straight_flush_total_count"){
                $updateArr[$key] = $val;
            }
            //四梅触顶值
            if( $key == "prefab_four_of_a_kind_max_count"){
                $updateArr[$key] = $val;
            }
            //五梅累计值
            if( $key == "prefab_five_of_a_kind_count"){
                $updateArr[$key] = $val;
            }
            //同花大顺累计值
            if( $key == "prefab_royal_flush_count"){
                $updateArr[$key] = $val;
            }
            //同花小顺累计值
            if( $key == "prefab_straight_flush_count"){
                $updateArr[$key] = $val;
            }
            //四梅累计值
            if( $key == "prefab_four_of_a_kind_T_T_count"){
                $updateArr[$key] = $val;
            }
        }
        if( !empty($updateArr) ){
            self::updateAll($updateArr,$whereBy);
        }
        return true;
    }

    /**
     * 获取这个类型下的所有压住分  的buff值和触顶值
     * @param $oddsType
     * @param $oddsTypeId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getList($oddsType, $oddsTypeId){
        $data = self::find()->where('odds_type = :odds_type and odds_type_id = :odds_type_id',
            array(':odds_type'=>$oddsType, ':odds_type_id'=>$oddsTypeId))->asArray()->all();
        //去掉所有的0
        $tool = new Tool();
        $rs = array();
        foreach ($data as $key =>$val){
            unset($val['id'],$val['is_default'],$val['odds_type'],$val['odds_type_id']);
            $rs[$val['bet_score']] = $tool->clearFloatZero($val);
        }
        return $rs;
    }

    /**
     *  初始化数据 根据 oddstype的类型不同 调用不同的方法
     * @param $oddsType
     */
    public function initInfo($oddsType,$oddsTypeIdArr){
        switch ($oddsType){
            case 1:
                $this->initUserInfo($oddsType,$oddsTypeIdArr);
                break;
            case 2:
                $this->initMachineInfo($oddsTypeIdArr);
                break;
            case 3:
                $this->initUserInfo($oddsType,$oddsTypeIdArr);
                break;
            default:
                return;
        }
        return;
    }

    /**
     * 按照类型初始化用户触顶置和buff值的数据
     * @param $oddsType
     */
    public function initUserInfo($oddsType,$accountIdArr)
    {
        $table = self::tableName();
        $isDefault = 2;
        //首先删除所有的用户数据
        if( !empty($accountIdArr) ){
            $inStr = "'".implode("','", $accountIdArr)."'";
            self::deleteAll("is_default = {$isDefault} and odds_type = :odds_type and odds_type_id not in ({$inStr})",[':odds_type'=>$oddsType]);
        }else{
            self::deleteAll("is_default = {$isDefault} and odds_type = :odds_type ",[':odds_type'=>$oddsType]);
        }

        //然后 初始化所有用户的玩家几率
        $sql = "
            insert into {$table} (
                is_default,
                odds_type,
                odds_type_id,
                bet_score,
                prefab_five_of_a_kind_count ,
                five_of_a_kind_total_count ,
                prefab_royal_flush_count,
                royal_flush_total_count ,
                prefab_straight_flush_count ,
                straight_flush_total_count ,
                prefab_four_of_a_kind_T_T_count ,
                prefab_four_of_a_kind_max_count 
                )
            select 
                '{$isDefault}',
                odds_dzb_count.odds_type,
                fivepk_account.account_id as odds_type_id,
                odds_dzb_count.bet_score,
                odds_dzb_count.prefab_five_of_a_kind_count ,
                odds_dzb_count.five_of_a_kind_total_count ,
                odds_dzb_count.prefab_royal_flush_count,
                odds_dzb_count.royal_flush_total_count ,
                odds_dzb_count.prefab_straight_flush_count ,
                odds_dzb_count.straight_flush_total_count ,
                odds_dzb_count.prefab_four_of_a_kind_T_T_count ,
                odds_dzb_count.prefab_four_of_a_kind_max_count 
            from fivepk_account
            LEFT JOIN odds_dzb_count on odds_dzb_count.is_default = 1 and odds_type = '{$oddsType}'
        ";
        if( !empty($accountIdArr) ){
            $inStr = "'".implode("','", $accountIdArr)."'";
            $sql .= "  where fivepk_account.account_id not in ({$inStr})";
        }
        $this->Tool->myLog("sql is:".$sql);
        Yii::$app->game_db->createCommand($sql)->query();
    }

    /**
     * 按照类型初始化  机台 触顶置和buff值的数据
     * @param $oddsType
     */
    public function initMachineInfo( $machineIdArr)
    {
        $table = self::tableName();
        $machineTable = $this->tableMachine;
        $isDefault = 2;
        //首先删除所有的用户数据
        if( !empty($machineIdArr) ){
            $inStr = "'".implode("','", $machineIdArr)."'";
            self::deleteAll("is_default = {$isDefault} and odds_type = :odds_type and odds_type_id in ({$inStr})",[':odds_type'=>2]);
        }else{
            self::deleteAll("is_default = {$isDefault} and odds_type = :odds_type ",[':odds_type'=>2]);
        }

        //然后 初始化所有用户的玩家几率
        $sql = "
            insert into {$table} (
                is_default,
                odds_type,
                odds_type_id,
                bet_score,
                prefab_five_of_a_kind_count ,
                five_of_a_kind_total_count ,
                prefab_royal_flush_count,
                royal_flush_total_count ,
                prefab_straight_flush_count ,
                straight_flush_total_count ,
                prefab_four_of_a_kind_T_T_count ,
                prefab_four_of_a_kind_max_count 
                )
            select 
                '{$isDefault}',
                odds_dzb_count.odds_type,
                machine.auto_id as odds_type_id,
                odds_dzb_count.bet_score,
                odds_dzb_count.prefab_five_of_a_kind_count ,
                odds_dzb_count.five_of_a_kind_total_count ,
                odds_dzb_count.prefab_royal_flush_count,
                odds_dzb_count.royal_flush_total_count ,
                odds_dzb_count.prefab_straight_flush_count ,
                odds_dzb_count.straight_flush_total_count ,
                odds_dzb_count.prefab_four_of_a_kind_T_T_count ,
                odds_dzb_count.prefab_four_of_a_kind_max_count 
            from {$machineTable}  as machine
            LEFT JOIN odds_dzb_count on odds_dzb_count.is_default = 1 and odds_type = 2
        ";
        if( !empty($machineIdArr) ){
            $inStr = "'".implode("','", $machineIdArr)."'";
            $sql .= "  where machine.auto_id  in ({$inStr})";
        }
        $this->Tool->myLog("sql is:".$sql);
        Yii::$app->game_db->createCommand($sql)->query();
    }

}
