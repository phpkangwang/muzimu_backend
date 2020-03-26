<?php

namespace common\models\game\paman;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use Yii;

//这个是只属于自己游戏的model，用来处理在不同游戏，相同方法，返回的不同的数据
class FivepkPlayerPamanSetting extends BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_paman_setting';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
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
            'id'                       => 'ID主键',
            'account_id'               => '用户id',
            'jp_accumulate_count'      => 'JP奖:此机台距离上次JP放奖次数',
            'jp_award_interval_count'  => 'JP奖:JP奖间隔局数(配置)',
            'jp_play_count'            => 'JP奖:JP奖放奖条件:小奖押注几次以下',
            'jp_play_count_rate'       => 'JP奖:小奖押注几次以下概率控制',
            'jp_open_limit_count'      => 'JP奖:JP奖最迟放奖延迟次数:到达上限直接放奖',
            'jp_pre_win_type'          => 'JP奖:随机选中的JP奖奖型:JP奖该奖型放掉后根据概率生成新的奖型(默认值四梅+Joker)',
            'jp_pre_win_type_rate'     => 'JP奖:随机选中的JP奖奖型:大奖出JP奖概率',
            'jp_accumulate_add_buff'   => 'JP奖:对应奖型每玩一次累加多少累计值;累加到jp_accumulate_buff',
            'jp_accumulate_total_buff' => 'JP奖:每种奖型对应JP奖的累计总的BUFF值:决定是否出奖和触顶值比较是否出奖(每次该奖型(小奖Jp不清)出奖后清零)',
        ];
    }

    /**
     * 获取配置默认值
     * @return mixed
     */
    public function getDefault()
    {
        $GlobalConfig = new \common\models\GlobalConfig();
        $data         = $GlobalConfig->getDataInType($GlobalConfig::Old_PLAYER_JP_TYPE, 'value');
        return json_decode($data['value'], true);
    }

    /**
     * 修改配置默认值
     * @param $data
     * @param $loginId
     * @return mixed
     * @throws MyException
     */

    public function updateDefault($data, $loginId)
    {
        $this->verifyPost($data);
        $GlobalConfig = new \common\models\GlobalConfig();
        $obj          = $GlobalConfig->findOneByField('type', $GlobalConfig::Old_PLAYER_JP_TYPE, true);
        $data         = array_merge(json_decode($obj->value, true), $data);
        $return       = $obj->add(array(
            'value'      => json_encode($data),
            'updated_at' => time(),
            'admin_id'   => $loginId
        ));

        return json_decode($return['value'], true);
    }

    /**
     * 增加默认
     * @param $accountId
     * @return mixed
     */
    public function initUserOdds($accountId)
    {
        $default               = $this->getDefault();
        self::deleteAll("account_id=:account_id",[':account_id'=>$accountId]);
        $default['account_id'] = $accountId;
        return $this->add($default);

    }

    /**
     * 修改多个
     * @param $post
     * @return bool
     * @throws MyException
     * @throws \yii\db\Exception
     */
    public function UserUpdateOddsJp($post)
    {
        $accountIds = explode(',', $post['accountIds']);
        if (empty($accountIds)) {
            throw new MyException(ErrorCode::ERROR_PARAM);
        }
        unset($post['accountIds']);
        $this->verifyPost($post);
        $OldPlayerJpChange    = new \common\models\OldPlayerJpChangePaman();
        $obj                  = $OldPlayerJpChange::find();
        $OldPlayerJpChangeAll = $obj->indexBy('account_id')->where(array('in', 'account_id', $accountIds))->all();
        $tr                   = self::getDb()->beginTransaction();
        foreach ($accountIds as $accountId) {
            if (isset($OldPlayerJpChangeAll[$accountId])) {
                $oldColumn = json_decode($OldPlayerJpChangeAll[$accountId]->column, true);
                $column    = array_merge($oldColumn, $post);
                $addObj    = $OldPlayerJpChangeAll[$accountId];
            } else {
                $column = $post;
                $addObj = $OldPlayerJpChange;
            }
            $addObj->add(array('account_id' => $accountId, 'column' => json_encode($column)));
        }
        self::updateAll($post, ['in', 'account_id', $accountIds]);
        $tr->commit();
        return true;
    }

    /**
     * 初始化全部
     */
    public function initUserAllOdds()
    {
        $default = $this->getDefault();
        $this::updateAll($default);
    }

    /**
     * 覆盖
     * @param $post
     * @return bool
     * @throws MyException
     */
    public function updateAllUserOdds($post)
    {
        if (!isset($post['type'])) {
            throw new MyException(ErrorCode::ERROR_PARAM);
        }
        $type = $post['type'];
        unset($post['type']);
        $this->verifyPost($post);
//        $default = $this->getDefault();
        $where = '';
        if ($type == 2) {
            $OldPlayerJpChange = new \common\models\OldPlayerJpChangePaman();
            $changeIds         = $OldPlayerJpChange->getIds();
            $where             = array('not in', 'account_id', $changeIds);
        }
        unset($post['type']);

        $this::updateAll($post, $where);

        return true;
    }

    /**
     * 验证数据
     * @param $post
     * @throws MyException
     */
    private function verifyPost(&$post)
    {
//        varDump(json_encode([1=>20, 2=>20, 3=>20, 4=>20]));
        if (isset($post['jp_play_count_rate'])) {
            $post['jp_play_count_rate'] = stripslashes($post['jp_play_count_rate']);
            $verifyList                 = [1, 2, 3, 4];
            $arr                        = json_decode($post['jp_play_count_rate'], true);
            if (!is_array($arr)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $keys = array_keys($arr);
            if (Tool::examineArrSurplusArr($keys, $verifyList)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
        }

        if (isset($post['jp_pre_win_type_rate'])) {
            $verifyList                   = [50, 200, 120, 500];
            $post['jp_pre_win_type_rate'] = stripslashes($post['jp_pre_win_type_rate']);
            $arr                          = json_decode($post['jp_pre_win_type_rate'], true);
            if (!is_array($arr)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $keys = array_keys($arr);
            if (Tool::examineArrSurplusArr($keys, $verifyList)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
        }

        $verifyList = array_keys($this->attributeLabels());
        $keys       = array_keys($post);
        if (Tool::examineArrSurplusArr($keys, $verifyList)) {
            throw new MyException(ErrorCode::ERROR_PARAM);
        }
    }


}
