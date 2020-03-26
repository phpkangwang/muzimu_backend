<?php

namespace common\models\activity\rank;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\Tool;
use Yii;
use yii\db\Exception;

class RankAwardData extends BaseModel
{
    const ITEM_TYPE_GOLD = 0;//道具类型 0 金币
    const ITEM_TYPE_AWARD = 1;//道具类型 1 奖券
    //默认数据
    public $data = [1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => '', 8 => '', 9 => '', 10 => '',];

    /**
     * 表名
     */
    public static function tableName()
    {
        return 'rank_award_data';
    }

    /**
     *  设置数据库链接
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
        return [];

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => '自增ID',
            'award'        => '奖励0金币 {"0":100,"1":100}',
            'ranking_type' => '1日 2周 3月',
            'order'        => '排名',
            'update_time'  => '修改时间',
            'operator'     => '操作的管理员',
            'active'       => '1:生效 2:不生效'
        ];
    }

    /**
     * @param $get
     * @return array
     * @throws Exception
     */
    public function getList($get)
    {
        if (Tool::isIssetEmpty($get['rankingType'])) {
            throw new Exception(ErrorCode::ERROR_PARAM);
        }

        $obj = self::find();
        $obj->select('id,award,ranking_type,order,update_time,operator,active');
        $obj->indexBy('id');
        $obj->orderBy('order asc');
        $data = $obj->where('ranking_type=' . intval($get['rankingType']))->asArray()->all();

        if (is_array($data)) {
            foreach ($data as &$value) {
                $award                       = json_decode($value['award'], true);
                $this->data[$value['order']] = $value['active'] == 1 ? $award : '';
            }
        }
        return $this->data;
    }

    /**
     * 添加/修改
     * @param $post
     * @param $loginName
     * @return array|bool
     * @throws Exception
     */
    public function rankAdd($post, $loginName)
    {
        if (Tool::isIssetEmpty($post['data'])
            || Tool::isIssetEmpty($post['rankingType'])
            || !in_array($post['rankingType'], [1, 2, 3])
        ) {
            throw new Exception(ErrorCode::ERROR_PARAM);
        }
        $data = json_decode(stripslashes($post['data']), true);
        if (empty($data)) {
            throw new Exception(ErrorCode::ERROR_PARAM);
        }
        $rankingType = $post['rankingType'];

        $time = time();
        $list = self::find()->where('ranking_type=:ranking_type', [':ranking_type' => $rankingType])->indexBy('order')->all();
        foreach ($data as $key => &$value) {
            if (
            isset($list[$key])
            ) {
                $award  = '';
                $active = 2;
                if (!Tool::isIssetEmpty($value[0])) {
                    $award = '{"0":' . $value[0];
                }
                if (!Tool::isIssetEmpty($value[1])) {
                    if (!empty($award)) {
                        $award .= ',';
                    } else {
                        $award .= '{';
                    }
                    $award .= '"1":' . $value[1];
                }
                if (!empty($award)) {
                    $active = 1;
                    $award  = $award . '}';
                }
                $setData    = [
                    'award'        => $award,
                    'ranking_type' => $rankingType,
                    'order'        => $key,
                    'operator'     => $loginName,
                    'update_time'  => $time,
                    'active'       => $active
                ];
                $returnData = $list[$key]->add(
                    $setData
                );
                if ($active == 1) {
                    $this->data[$key] = $returnData['award'];
                }
            }

        }

        return $this->data;
    }
}
