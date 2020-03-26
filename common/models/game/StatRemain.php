<?php
namespace common\models\game;

use Yii;

/**
 * This is the model class for table "stat_remain".
 *
 * @property string $id
 * @property integer $dru
 * @property integer $active
 * @property double $second_day
 * @property double $third_day
 * @property double $seventh_day
 * @property double $fourteen_day
 * @property double $thirtieth_day
 * @property string $stat_time
 * @property string $add_time
 */
class StatRemain extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stat_remain';
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
            [['dru', 'active'], 'integer'],
            [['second_day', 'third_day', 'seventh_day', 'fourteen_day', 'thirtieth_day'], 'number'],
            [['stat_time', 'add_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dru' => 'Dru',
            'active' => 'Active',
            'second_day' => 'Second Day',
            'third_day' => 'Third Day',
            'seventh_day' => 'Seventh Day',
            'fourteen_day' => 'Fourteen Day',
            'thirtieth_day' => 'Thirtieth Day',
            'stat_time' => 'Stat Time',
            'add_time' => 'Add Time',
        ];
    }

    /**
     * 分页
     * @return array
     */
    public function page($pageNo, $pageSize, $where)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->where($where)->offset($offset)->orderBy('id desc')->limit($limit)->asArray()->all();
    }

    /**
     * 分页数量
     * @return array
     */
    public function pageCount( $where )
    {
        return self::find()->where($where)->count();
    }
 
}
