<?php
namespace common\models\activity\sign;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\Tool;
use Yii;
use yii\db\Exception;

class ActivityListSign extends BaseModel
{
    const ITEM_TYPE_GOLD = 0;//道具类型 0 金币
    const ITEM_TYPE_AWARD = 1;//道具类型 1 奖券
    //默认数据
    public $data = [
        self::ITEM_TYPE_GOLD => [1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => '',],
        self::ITEM_TYPE_AWARD => [1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => '',],
    ];

    /**
     * 表名
     */
    public static function tableName()
    {
        return 'activity_list_sign';
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
            'id' => '自增ID',
            'item_type' => '道具类型0金币',
            'day' => '第几天',
            'num' => '道具数量',
            'update_time' => '修改时间',
            'operator' => '操作的管理员',
        ];
    }

    /**
     * 列表
     * @param $get
     * @return mixed
     */
    public function getList($get)
    {
        $potion = [
            'order' => 'day asc',
            'select' => "id,item_type,day,num,update_time,operator",
            'indexBy' => 'id'
        ];

        //这段是PDO where
        {
            $pdo = [];
            $where = '';
            if (!empty($where)) {
                $potion['where'] = $where;
                $potion['pdo'] = $pdo;
            }
        }

        $list = array_keys($this->data);
        $data = $this->pageList(
            Tool::examineEmpty($get['pageNo'], 1)
            , Tool::examineEmpty($get['pageSize'], 8)
            , $potion
        );

        if (is_array($data)) {
            foreach ($data as $value) {
                if (in_array($value['item_type'], $list)) {
                    $this->data[$value['item_type']][$value['day']] = empty($value['num']) ? '' : $value['num'];
                }
            }
        }
        return $this->data;
    }

    /**
     * 添加/修改
     * @param $post
     * @return mixed
     */
    public function signAdd($post,$loginName)
    {
        if (Tool::isIssetEmpty($post['data'])) {
            throw new Exception( ErrorCode::ERROR_PARAM );
        }

        $data = json_decode(stripslashes($post['data']), true);

        if (empty($data)) {
            return false;
        }
        $time = time();
        $list = self::find()->indexBy('day')->all();
        foreach ($data as $key => $value) {
            if (isset($list[$key])
                && isset($value['item_type'])
                && isset($value['num'])
                && in_array($value['item_type'], [0, 1])
                && ($value['num'] >= 0 && $value['num'] <= 9999999)
            ) {
                $data[$key] = $list[$key]->add(['item_type' => $value['item_type'], 'num' => $value['num'], 'operator' => $loginName, 'update_time' => $time]);
                $this->data[$value['item_type']][$key] = empty($value['num']) ? '' : $value['num'];
            }
        }
        return $this->data;
    }
}
