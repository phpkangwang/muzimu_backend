<?php

namespace common\models;

use backend\models\Account;
use backend\models\AdminUser;
use backend\models\BaseModel;
use backend\models\Tool;
use common\models\game\base\GameBase;
use Yii;

/**
 * This is the model class for table "machine_path".
 *
 * @property integer $id
 * @property string $machine_id
 * @property string $before
 * @property string $after
 * @property integer $created_by
 * @property integer $created_at
 */
class MachinePath extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'machine_path';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['before', 'after'], 'string'],
            [['created_by', 'created_at'], 'integer'],
            [['machine_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'id',
            'machine_id' => '机台编号',
            'before'     => '操作前',
            'after'      => '操作后',
            'created_by' => '创建人',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 关联创建用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Account::className(), ['id' => 'created_by'])->select($this->Account->baseColumns);
    }

    /**
     * @desc 创建
     * @param $machine_id
     * @param $before
     * @param $after
     * @param $created_by
     * @return bool
     */
    public static function Create($machine_id, $before, $after, $created_by)
    {
        $model             = new MachinePath();
        $model->machine_id = $machine_id;
        $model->before     = $before;
        $model->after      = $after;
        $model->created_by = $created_by;
        $model->created_at = time();
        if ($model->validate() && $model->save()) {
            return true;
        } else {
            echo '<pre>';
            var_dump($model);
            var_dump($model->errors);
            die;
            return false;
        }
    }

    /**
     *  分页
     * @param $pageNo
     * @param $pageSize
     * @param $where
     * @param $gameName  游戏名称
     * @return array
     */
    public function page($pageNo, $pageSize, $where, $gameName)
    {
        $GameBaseObj     = new GameBase();
        $GameObj         = $GameBaseObj->initGameObj($gameName);

        if ($gameName == 'PAM') {
            //$gameModelPath = Yii::$app->params[$chineseGameName]['winTypeModel'];
            //$GameObj->getModelMachine();
        }else{
            $gameModel = $GameObj->getModelMachine();
        }

        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo - 1) * $pageSize;
        $data     = self::find()->joinWith('user')->where($where)->orderBy('id DESC')->offset($offset)->limit($limit)->asArray()->all();
        foreach ($data as $key => $val) {
            $data[$key]['path'] = array();
            $beforeDatas        = json_decode($val['before'], true);
            $afterDatas         = json_decode($val['after'], true);

            if ($gameName == 'BAO') {
                $this->baoPath($data[$key]['path'], $beforeDatas, $afterDatas);
            } else {

                foreach ($afterDatas as $afterDataKey => $afterData) {

                    if ($gameName != 'PAM') {
                        if ($gameName == 'GHR') {
                            if (is_array($afterData)) {
                                $afterData = Tool::json_encode($afterData);
                            }
                        }
                        foreach ($beforeDatas as $beforeDataKey => $beforeData) {
                            if ($afterDataKey == $beforeDataKey && $beforeData != $afterData) {
                                $data[$key]['path'][] =
                                    $gameModel->getAttributeLabel($beforeDataKey)
                                    . " :" . $beforeData
                                    . " -> " . $afterData;
                            }
                        }
                    } else {
                        foreach ($afterData as $k => $v) {
                            if (isset($beforeDatas[$afterDataKey][$k]) && $v != $beforeDatas[$afterDataKey][$k]) {
                                $data[$key]['path'][] = $afterDataKey . $gameModel->getAttributeLabel($k) . ':' . $beforeDatas[$afterDataKey][$k] . ' ->' . $v;
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    private static function baoPath(&$path, $beforeDatas, $afterDatas)
    {
        $return = array(
            'difficultyMeasure'    => '切换概率',//
            'jpDoubleRate'         => 'jp概率',//
            'machineResetWinTop'   => '机台几率重置',//
            'random'               => array(
                'winTypeRate' => '普通一灯 , BAR , 特殊送灯, 幸运灯',
                'specialList' => '特殊灯下拉列表',//
                'luckList'    => '幸运灯下拉列表',//
                'barList'     => 'bar下拉列表',//
                'peakList'    => '大满贯下拉列表',
                'peak'        => '大满贯下拉列表'
            ),//随机奖
            //普通奖
            'general'              => [
                '1'  => '普通奖位置1',
                '2'  => '普通奖位置2',
                '3'  => '普通奖位置3',
                '4'  => '普通奖位置4',
                '5'  => '普通奖位置5',
                '6'  => '普通奖位置6',
                '7'  => '普通奖位置7',
                '8'  => '普通奖位置8',
                '9'  => '普通奖位置9',
                '10' => '普通奖位置10',
                '11' => '普通奖位置11',
                '12' => '普通奖位置12',
                '13' => '普通奖位置13',
                '14' => '普通奖位置14',
                '15' => '普通奖位置15',
                '16' => '普通奖位置16',
                '17' => '普通奖位置17',
                '18' => '普通奖位置18',
                '19' => '普通奖位置19',
                '20' => '普通奖位置20',
                '21' => '普通奖位置21',
                '22' => '普通奖位置22',
                '23' => '普通奖位置23',
                '24' => '普通奖位置24',

            ],//
            'lightMultiplyingRate' => '倍率灯占比',//
            'BAR'                  => [
                'big_bar_peak_value_section'             => '实押70~99BAR的触顶区间',//
                'middle_bar_peak_value_section'          => '实押40~69BAR的触顶区间',//
                'small_bar_peak_value_section'           => '实押1~39BAR的触顶区间',//
                'without_bar_peak_value_section'         => '空押BAR的触顶区间',//
                'mechine_peak_score_section'             => '保底大BAR的触顶区间',//
                'big_bar_peak_value_and_accum_value'     => '实押70~99BAR的触顶值和当前累计值',//
                'middle_bar_peak_value_and_accum_value'  => '实押40~69BAR的触顶值和当前累计值',//
                'small_bar_peak_value_and_accum_value'   => '实押1~39BAR的触顶值和当前累计值',//
                'without_bar_peak_value_and_accum_value' => '空押BAR的触顶值和当前累计值',//
                'mechine_peak_value_and_win_score'       => '保底大BAR的触顶值和当前累计值',//
                'big_bar_peak_value_and_BAR'             => '70~99BAR',
                'middle_bar_peak_value_and_BAR'          => '40~69BAR',
                'small_bar_peak_value_and_BAR'           => '实押1~39BAR',
                'without_bar_peak_value_and_BAR'         => '空押BAR',
                'mechine_peak_value_and_BAR'             => '保底大BAR',
                'vole_peak_value_section'                => '大满贯的触顶区间',//
                'vole_peak_value_and_accum_value'        => '大满贯触顶值和当前累计值',//

            ],//5大条BAR的累积相关的格子
        );


        $difficulty = [1 => '简单=>', 2 => '困难=>'];

        foreach ($difficulty as $k => $v) {
            if (isset($beforeDatas[$k])) {
                foreach ($beforeDatas[$k] as $key => $value) {

                    if ($key == 'countPoint') {
                        continue;
                    }

                    if (is_array($value) && ($key == 'BAR' || $key == 'random' || $key == 'general')) {
                        foreach ($value as $key2 => $value2) {

                            if (!isset($afterDatas[$k][$key][$key2])) {
                                continue;
                            }
                            if (
                                $beforeDatas[$k][$key][$key2]
                                !=
                                $afterDatas[$k][$key][$key2]
                            ) {
                                $path[] = $v . $return[$key][$key2] . ':' . self::serializeArr($beforeDatas[$k][$key][$key2]) . '->' . self::serializeArr($afterDatas[$k][$key][$key2]);
                            }
                        }
                    } else {
                        if (!isset($afterDatas[$k][$key]) || !isset($return[$key])) {
                            continue;
                        }
                        if ($beforeDatas[$k][$key] != $afterDatas[$k][$key]) {
                            $path[] = $v .
                                $return[$key] . ':' .
                                self::serializeArr($beforeDatas[$k][$key]) . '->' .
                                self::serializeArr($afterDatas[$k][$key]);
                        }
                    }
                }
            }
        }


    }


    private static function serializeArrayDiff($arr1, $arr2)
    {
        return array_filter($arr1, function ($v) use ($arr2) {
            return !in_array($v, $arr2);
        });
    }


    private static function serializeArr($data)
    {
        return (is_array($data) ? json_encode($data) : (empty($data) ? '空' : $data));
    }

    /**
     *  获取最大条数
     */
    public function accountNum($where)
    {
        return self::find()->joinWith('user')->where($where)->count();
    }

}
