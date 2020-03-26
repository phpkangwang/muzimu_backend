<?php


namespace common\models\game\snow_leopard;


use backend\models\ErrorCode;
use backend\models\MyException;
use common\models\game\FivepkPlayerInfo;
use common\models\MachinePath;
use common\models\OddsChangePath;

class SeoMachineSetRate
{
    /*每个机台的奖概率对应 snow_leopard_seo_machine(一条)  snow_leopard_seo_machine_wintype(多条) */
    private $seoMachine;//雪豹机台表 对应一条
    private $seoMachineWinType;//雪豹机台表对应奖型  对应多条

    public $seoMachineId;//机台编号
    public $level;//困难度 1简单 2困难
    public $post;//修改
    public $loginId = 0;

    public $default = false;//默认几率

    private function selectInit()
    {

        if ($this->default) {
            $SnowLeopardSeoMachine       = new  BaoDefaultOdds();//一条
            $SnowLeopardSeoMachineWinype = new  SnowLeopardSeoMachineWintypeDefault();//多条
        } else {
            $SnowLeopardSeoMachine       = new  BaoMachine();//一条
            $SnowLeopardSeoMachineWinype = new  SnowLeopardSeoMachineWintype();//多条
        }

        $where1 = '';
        $where2 = "difficulty_level={$this->level} or  difficulty_level=0";
        if (!$this->default) {
            $where1 = "seo_machine_id='{$this->seoMachineId}'";
            $where2 = "seo_machine_id='{$this->seoMachineId}' and difficulty_level={$this->level} or (seo_machine_id='{$this->seoMachineId}' and difficulty_level=0)";
        }

        $this->seoMachine        = $SnowLeopardSeoMachine::find()->where($where1)->asArray()->one();
        $this->seoMachineWinType = $SnowLeopardSeoMachineWinype::find()->where($where2)->indexBy('win_type')->asArray()->all();
    }

    private function selectObjInit()
    {
        if ($this->default) {
            $SnowLeopardSeoMachine       = new  BaoDefaultOdds();//一条
            $SnowLeopardSeoMachineWinype = new  SnowLeopardSeoMachineWintypeDefault();//多条
        } else {
            $SnowLeopardSeoMachine       = new  BaoMachine();//一条
            $SnowLeopardSeoMachineWinype = new  SnowLeopardSeoMachineWintype();//多条
        }

        $where1 = '';
        $where2 = "difficulty_level={$this->level} or  difficulty_level=0";
        if (!$this->default) {
            $where1 = "seo_machine_id='{$this->seoMachineId}'";
            $where2 = "seo_machine_id='{$this->seoMachineId}' and difficulty_level={$this->level} or (seo_machine_id='{$this->seoMachineId}' and difficulty_level=0)";
        }

        $this->seoMachine        = $SnowLeopardSeoMachine::find()->where($where1)->one();
        $this->seoMachineWinType = $SnowLeopardSeoMachineWinype::find()->where($where2)->indexBy('win_type')->all();

//        varDump($this->seoMachineWinType);

    }


    //查看几率列表
    public function showList()
    {

        $this->selectInit();
        $return = [];
        if ($this->level == 1) {
            $winTypeRate          = json_decode($this->seoMachine['simple_win_type_rate'], true);
            $lightMultiplyingRate = json_decode($this->seoMachine['simple_light_multiplying_rate'], true);
        } elseif ($this->level == 2) {
            $winTypeRate          = json_decode($this->seoMachine['difficult_win_type_rate'], true);
            $lightMultiplyingRate = json_decode($this->seoMachine['difficulty_light_multiplying_rate'], true);
        }

        $difficultyMeasure = $this->seoMachine['difficulty_measure'] * 100;//显示整数

        $jpDoubleRate = json_decode($this->seoMachine['jp_double_rate'], true);
        $jpDoubleRate = $jpDoubleRate[$this->level] / 100;

        $return = array(
            'difficultyMeasure'    => $difficultyMeasure,//切换概率
            'jpDoubleRate'         => $jpDoubleRate,//jp概率
            'random'               => array(
                'winTypeRate' => $winTypeRate,/*    普通一灯 , BAR , 特殊送灯(特殊送灯是可以手填调整,而其他三个是灰色的不可调整 ) , 幸运灯,大满贯' */
                'specialList' => [],//特殊灯下拉列表
                'luckList'    => [],//幸运灯下拉列表
                'barList'     => [],//bar下拉列表
                'peakList'    => [],//大满贯下拉列表
            ),//随机奖
            'general'              => [],//普通奖
            'machineResetWinTop'   => $this->seoMachine['machine_reset_win_top'],//盈利触顶值
            'nickName'             => '',//正在玩游戏用户的昵称
            'lightMultiplyingRate' => $lightMultiplyingRate,//倍率灯占比
            //触顶区间
            'BAR'                  => [
                'big_bar_peak_value_section'     => json_decode($this->seoMachine['big_bar_peak_value_section'], true),//实押70~99BAR的触顶区间
                'middle_bar_peak_value_section'  => json_decode($this->seoMachine['middle_bar_peak_value_section'], true),//实押40~69BAR的触顶区间
                'small_bar_peak_value_section'   => json_decode($this->seoMachine['small_bar_peak_value_section'], true),//实押1~39BAR的触顶区间
                'without_bar_peak_value_section' => json_decode($this->seoMachine['without_bar_peak_value_section'], true),//空押BAR的触顶区间
                'mechine_peak_score_section'     => json_decode($this->seoMachine['mechine_peak_score_section'], true),//保底大BAR的触顶区间
                'vole_peak_value_section'        => json_decode($this->seoMachine['vole_peak_value_section'], true),//大满贯的触顶区间
            ],//5大条BAR的累积相关的格子
        );

        if (!$this->default) {
            //如果是普通几率

            $accountId = 0;
            if (isset($this->seoMachine->account_id)) {
                $accountId = $this->seoMachine->account_id;
            } elseif (isset($this->seoMachine['account_id'])) {
                $accountId = $this->seoMachine['account_id'];
            }
            if (!empty($accountId)) {
                $nickName = FivepkPlayerInfo::find()->where("account_id=$accountId")->select('nick_name')->one();
                if (isset($nickName->nick_name)) {
                    $return['nickName'] = $nickName->nick_name;
                }
            }

            //机台游戏机率
            if ($this->seoMachine['player_play_point'] == 0) {
                $return['countPoint'] = '0%';
            } else {
                $return['countPoint'] = (round($this->seoMachine['player_win_point'] / $this->seoMachine['player_play_point'], 2) * 100) . '%';
            }

            //盈利
            $return['profit'] = $this->seoMachine['player_play_point'] - $this->seoMachine['player_win_point'];


            $return['BAR'] += [
                'big_bar_peak_value_and_accum_value'     => json_decode($this->seoMachine['big_bar_peak_value_and_accum_value'], true),//实押70~99BAR的触顶值和当前累计值
                'middle_bar_peak_value_and_accum_value'  => json_decode($this->seoMachine['middle_bar_peak_value_and_accum_value'], true),//实押40~69BAR的触顶值和当前累计值
                'small_bar_peak_value_and_accum_value'   => json_decode($this->seoMachine['small_bar_peak_value_and_accum_value'], true),//实押1~39BAR的触顶值和当前累计值
                'without_bar_peak_value_and_accum_value' => json_decode($this->seoMachine['without_bar_peak_value_and_accum_value'], true),//空押BAR的触顶值和当前累计值
                'mechine_peak_value_and_win_score'       => json_decode($this->seoMachine['mechine_peak_value_and_win_score'], true),//保底大BAR的触顶值和当前累计值
                'vole_peak_value_and_accum_value'        => json_decode($this->seoMachine['vole_peak_value_and_accum_value'], true),//大满贯的累计值
            ];
        }

        foreach ($this->seoMachineWinType as $value) {
            if ($value['win_kind'] == 100) {
                //普通
                $arr               = json_decode($value['light_random_rate'], true);
                $return['general'] = $return['general'] + $arr;
//                foreach ($arr as $key => $val) {
//                    $return['general'][$key] = $val;
//                }
            } else if ($value['win_kind'] == 200) {
                //特殊灯
                $return['random']['specialList'][$value['win_type']]['list']          = json_decode($value['light_random_rate'], true);
                $return['random']['specialList'][$value['win_type']]['lightTypeRate'] = $value['light_type_rate'];
            } else if ($value['win_kind'] == 300) {
                //幸运灯
                $return['random']['luckList'][$value['win_type']]['list']          = json_decode($value['light_random_rate'], true);
                $return['random']['luckList'][$value['win_type']]['lightTypeRate'] = $value['light_type_rate'];
            }


            switch ($value['win_type']) {
                case 403:
                    $return['random']['barList'] = json_decode($value['light_random_rate'], true);
                    break;
                case 501:
                    $return['random']['peakList'] = json_decode($value['light_random_rate'], true);
                    break;
                case 1001:
                    //空押BAR
                    $return['BAR']['without_bar_peak_value_and_BAR'] = json_decode($value['light_random_rate'], true);
                    break;
                case 1002:
                    //实押1~39BAR
                    $return['BAR']['small_bar_peak_value_and_BAR'] = json_decode($value['light_random_rate'], true);
                    break;
                case 1003:
                    //40~69BAR
                    $return['BAR']['middle_bar_peak_value_and_BAR'] = json_decode($value['light_random_rate'], true);
                    break;
                case 1004:
                    //70~99BAR
                    $return['BAR']['big_bar_peak_value_and_BAR'] = json_decode($value['light_random_rate'], true);
                    break;
                case 1005:
                    //保底大BAR
                    $return['BAR']['mechine_peak_value_and_BAR'] = json_decode($value['light_random_rate'], true);
                    break;
                default;
            }
        }
        return $return;
    }

    //修改几率
    public function updateRate()
    {
        try {

            /*start 机台轨迹*/
            $before = $this->showList();
            /*end 机台轨迹*/

            $post = &$this->post;
            $this->selectObjInit();
            if (empty($this->seoMachine) || empty($this->seoMachineWinType)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $seoMachineData = [];
            $winTypeRate    = [
                100 => 0,//win_kind为100，win_type为1~7的light_type_rate字段之和
                200 => 0,//特殊送灯的值，手填整数
                300 => 0,//win_kind为100，win_type为9的light_type_rate
                400 => 0,//win_kind为100，win_type为8的light_type_rate
                500 => 0,//win_kind为100，win_type为501的light_type_rate
            ];//求和

            //切换概率
            if (isset($post['difficultyMeasure']) && is_numeric($post['difficultyMeasure'])) {
                if ($post['difficultyMeasure'] > 100 || $post['jpDoubleRate'] < 0) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }
                $seoMachineData['difficulty_measure'] = $post['difficultyMeasure'] / 100;
            }

            //jp概率
            if (isset($post['jpDoubleRate']) && is_numeric($post['jpDoubleRate'])) {
                if ($post['jpDoubleRate'] > 100 || $post['jpDoubleRate'] < 0) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }

                $jpDoubleRate                     = json_decode($this->seoMachine->jp_double_rate, true);
                $jpDoubleRate[$this->level]       = $post['jpDoubleRate'] * 100;
                $seoMachineData['jp_double_rate'] = self::json_encode($jpDoubleRate);
            }

            //随机灯
            //特殊灯修改
            if (isset($post['special'])) {
                //difficult_win_type_rate simple_win_type_rate
                $winTypeRate[200] = intval(($post['special']));
            }

            //大满贯修改
            if (isset($post['peakValue'])) {
                //difficult_win_type_rate simple_win_type_rate
                $winTypeRate[500] = intval(($post['peakValue']));
            }

            $arr = [
                201, 202, 203, 204,//下拉特殊灯列表修改
                301, 302,//下拉幸运灯
                403,//下拉bar
                501,//大满贯
                1, 2, 3, 4, 5, 6, 7, 8, 9,//普通灯
                1001, 1002, 1003, 1004, 1005, //BAR
            ];

            foreach ($arr as $value) {
                if (isset($post[$value]['light_type_rate']) && isset($post[$value]['light_random_rate'])) {

                    if ($value <= 7 && $value >= 1) {
                        $winTypeRate[100] += $post[$value]['light_type_rate'];
                    }

                    if ($value == 9) {
                        $winTypeRate[300] += $post[$value]['light_type_rate'];
                    }

                    if ($value == 8) {
                        $winTypeRate[400] += $post[$value]['light_type_rate'];

                        $light_random_rate = $post[$value]['light_random_rate'];

                        $this->seoMachineWinType[401]->add(
                            [
                                'light_type_rate' => $light_random_rate[2],
                            ]
                        );

                        $this->seoMachineWinType[402]->add(
                            [
                                'light_type_rate' => $light_random_rate[24],
                            ]
                        );

                        $this->seoMachineWinType[403]->add(
                            [
                                'light_type_rate' => $light_random_rate[1],
                            ]
                        );

                    }

                    if (in_array($value, [401, 501])) {
                        //只修改light_random_rate
                        $this->seoMachineWinType[$value]->add(
                            [
                                'light_random_rate' => self::json_encode($post[$value]['light_random_rate']),
                            ]
                        );
                    } else {
                        $this->seoMachineWinType[$value]->add(
                            [
                                'light_type_rate'   => $post[$value]['light_type_rate'],
                                'light_random_rate' => self::json_encode($post[$value]['light_random_rate']),
                            ]
                        );
                    }


                }
            }


            //倍率灯占比
            if (isset($post['lightMultiplyingRate'])) {
                if ($this->level == 1) {
                    $seoMachineData['simple_light_multiplying_rate'] = self::json_encode($post['lightMultiplyingRate']);
                } else {
                    $seoMachineData['difficulty_light_multiplying_rate'] = self::json_encode($post['lightMultiplyingRate']);
                }
            }


            //bar 相关 区间 修改
            $arr = [
                'big_bar_peak_value_section',
                'middle_bar_peak_value_section',
                'small_bar_peak_value_section',
                'without_bar_peak_value_section',
                'mechine_peak_score_section',
                'vole_peak_value_section'
            ];

            if (!$this->default) {
                //bar 触顶值
                $arr = array_merge($arr, [
                    'big_bar_peak_value_and_accum_value',
                    'middle_bar_peak_value_and_accum_value',
                    'small_bar_peak_value_and_accum_value',
                    'without_bar_peak_value_and_accum_value',
                    'mechine_peak_value_and_win_score',
                    'vole_peak_value_and_accum_value'
                ]);
            }


            foreach ($arr as $value) {
                if (isset($post[$value])) {
                    $seoMachineData[$value] = self::json_encode($post[$value]);
                }
            }

            if ($this->level == 1) {
                $seoMachineData['simple_win_type_rate'] = self::json_encode($winTypeRate);
            } else {
                $seoMachineData['difficult_win_type_rate'] = self::json_encode($winTypeRate);
            }

            //机台几率重置盈利
            if (isset($post['machineResetWinTop'])) {
                $seoMachineData['machine_reset_win_top'] = intval($post['machineResetWinTop']);
            }


            $this->seoMachine->add($seoMachineData);
            $after = $this->showList();
            //MachinePath::Create($this->seoMachineId, json_encode(['title' => 'normal', $this->level => $before]), json_encode(['title' => 'normal', $this->level => $after]), $this->loginId);//写入历史记录
            $content = array();
            $this->baoPath($content, ['title' => 'normal', $this->level => $before], ['title' => 'normal', $this->level => $after]);
            if ( !$this->default) {
                $OddsChangePathModel = new OddsChangePath();
                $postData            = array(
                    'game_type' => \Yii::$app->params['xb'],
                    'type'      => $OddsChangePathModel->typeMachine,
                    'type_id'   => $this->seoMachineId,
                    'content'   => json_encode($content, JSON_UNESCAPED_UNICODE),
                );
                $OddsChangePathModel->add($postData);
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


    //默认几率
    public function getDefaultOddsRate()
    {
        $this->default = true;
        return $this->showList();
    }

    //修改默认几率
    public function updateDefaultOddsRate()
    {
        $this->default = true;
        $this->updateRate();
    }

    //通过默认几率修改机台几率
    public function updateRateForDefault()
    {
        $SnowLeopardSeoMachineDefault       = new  BaoDefaultOdds();//一条
        $SnowLeopardSeoMachineWinypeDefault = new  SnowLeopardSeoMachineWintypeDefault();//多条

        $SnowLeopardSeoMachine       = new  BaoMachine();//一条
        $SnowLeopardSeoMachineWinype = new  SnowLeopardSeoMachineWintype();//多条

        $machines = $this->post['machines'];

        foreach ($machines as $seoMachineId) {
            $this->seoMachineId = $seoMachineId;
            /*start 机台轨迹*/
            $this->level = 1;
            $before      = $this->showList();
            $this->level = 2;
            $before2     = $this->showList();
            /*end 机台轨迹*/

            $SnowLeopardSeoMachineValue       = $SnowLeopardSeoMachine::find()->where("seo_machine_id='$seoMachineId'")->one();
            $SnowLeopardSeoMachineWinypeValue = $SnowLeopardSeoMachineWinype::find()->where("seo_machine_id='$seoMachineId'")->all();//机台奖
            $room_info_list_id                = $SnowLeopardSeoMachineValue->room_info_list_id;
            if (!isset($SnowLeopardSeoMachineDefaultValue[$room_info_list_id])) {
                $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]       = $SnowLeopardSeoMachineDefault::find()->where("room_info_list_id='{$room_info_list_id}'")->asArray()->one();
                $SnowLeopardSeoMachineWinypeDefaultValue[$room_info_list_id] = $SnowLeopardSeoMachineWinypeDefault::find()->where("room_info_list_id='{$room_info_list_id}'")->asArray()->all();
                foreach ($SnowLeopardSeoMachineWinypeDefaultValue[$room_info_list_id] as $value) {
                    $SnowLeopardSeoMachineWinypeDefaultValue[$room_info_list_id]['list'][$value['difficulty_level']][$value['win_type']] = $value;
                }
            }

            //机台奖修改
            foreach ($SnowLeopardSeoMachineWinypeValue as $key => $value) {
                $value->add([
                    'light_type_rate'   => $SnowLeopardSeoMachineWinypeDefaultValue[$room_info_list_id]['list'][$value['difficulty_level']][$value['win_type']]['light_type_rate'],
                    'light_random_rate' => $SnowLeopardSeoMachineWinypeDefaultValue[$room_info_list_id]['list'][$value['difficulty_level']][$value['win_type']]['light_random_rate'],
                ]);
            }

            $SnowLeopardSeoMachineValue->add(
                [
                    'difficulty_measure'                => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['difficulty_measure'],
                    'simple_win_type_rate'              => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['simple_win_type_rate'],
                    'difficult_win_type_rate'           => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['difficult_win_type_rate'],
                    'simple_light_multiplying_rate'     => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['simple_light_multiplying_rate'],
                    'difficulty_light_multiplying_rate' => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['difficulty_light_multiplying_rate'],
                    'mechine_peak_score_section'        => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['mechine_peak_score_section'],
                    'big_bar_peak_value_section'        => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['big_bar_peak_value_section'],
                    'middle_bar_peak_value_section'     => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['middle_bar_peak_value_section'],
                    'small_bar_peak_value_section'      => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['small_bar_peak_value_section'],
                    'without_bar_peak_value_section'    => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['without_bar_peak_value_section'],
                    'jp_double_rate'                    => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['jp_double_rate'],
                    'vole_peak_value_section'           => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['vole_peak_value_section'],
                    'machine_reset_win_top'             => $SnowLeopardSeoMachineDefaultValue[$room_info_list_id]['machine_reset_win_top']
                ]
            );

            $this->level = 1;
            $after       = $this->showList();
            $this->level = 2;
            $after2      = $this->showList();


            //MachinePath::Create($seoMachineId, json_encode(['title' => 'default', 1 => $before, 2 => $before2]), json_encode(['title' => 'default', 1 => $after, 2 => $after2]), $this->loginId);
            $content = "";
            $this->baoPath($content, ['title' => 'default', 1 => $before, 2 => $before2], ['title' => 'default', 1 => $after, 2 => $after2]);
            $OddsChangePathModel = new OddsChangePath();
            $postData            = array(
                'game_type' => \Yii::$app->params['xb'],
                'type'      => $OddsChangePathModel->typeMachine,
                'type_id'   => $seoMachineId,
                'content'   => json_encode($content, JSON_UNESCAPED_UNICODE),
            );
            $OddsChangePathModel->add($postData);
        }

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

    private static function serializeArr($data)
    {
        return (is_array($data) ? json_encode($data) : (empty($data) ? '空' : $data));
    }

    //统一json转换
    public static function json_encode($data)
    {
        return json_encode($data, JSON_NUMERIC_CHECK);
    }

}