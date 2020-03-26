<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-14
 * Time: 17:17
 */

namespace backend\services;


use yii\base\Model;

class FivepkDefaultFivepkService extends Model
{
    public static $card = array(
        1=>'方块A',2=>'方块2',3=>'方块3',4=>'方块4',5=>'方块5',6=>'方块6',7=>'方块7',8=>'方块8',9=>'方块9',10=>'方块10',11=>'方块J',12=>'方块Q',13=>'方块K',
        14=>'梅花A',15=>'梅花2',16=>'梅花3',17=>'梅花4',18=>'梅花5',19=>'梅花6',20=>'梅花7',21=>'梅花8',22=>'梅花9',23=>'梅花10',24=>'梅花J',25=>'梅花Q',26=>'梅花K',
        27=>'红桃A',28=>'红桃2',29=>'红桃3',30=>'红桃4',31=>'红桃5',32=>'红桃6',33=>'红桃7',34=>'红桃8',35=>'红桃9',36=>'红桃10',37=>'红桃J',38=>'红桃Q',39=>'红桃K',
        40=>'黑桃A',41=>'黑桃2',42=>'黑桃3',43=>'黑桃4',44=>'黑桃5',45=>'黑桃6',46=>'黑桃7',47=>'黑桃8',48=>'黑桃9',49=>'黑桃10',50=>'黑桃J',51=>'黑桃Q',52=>'黑桃K',
        53=>'鬼',54=>'鬼',55=>'鬼',56=>'鬼',""=>'',
    );
    public static function getRandomType($random)
    {
        $user = \Yii::$app->user->identity;
        $type = null;
        if($user->white_list == 1){
            switch ($random) {
                case 0: $type = '机台累积';break;
                case 1: $type = '机台随机';break;
                case 2: $type = '房间累积';break;
                case 3: $type = '新人奖';break;
            }
        }
        return $type;
    }

    /**
     * @desc 获得牌型
     * @param $cards
     * @return bool|string
     */
    public static function getCards($cards)
    {
        $arr = explode(",", $cards);
        $str = '';
        foreach ($arr as $val){
            $str .= ','.self::$card[trim($val)];
        }
        $str = substr($str,1);
        return $str;
    }

    /**
     * 获得一手牌型
     * @return string
     */
    public static function getFirstCardType($game_type,$first_win_type)
    {
        if ($game_type == \Yii::$app->params['fire_phoenix']) {
            switch($first_win_type){
                case -3: $result="小一对"; break;
                case 1: $result="大一对"; break;
                case 2: $result="两对"; break;
                case 3: $result="三条"; break;
                case 5: $result="顺子"; break;
                case 7: $result="同花"; break;
                case 10: $result="葫芦"; break;
                case 50: $result="小四梅"; break;
                case 80: $result="大四梅"; break;
                case 120: $result="同花小顺"; break;
                case 250: $result="五梅"; break;
                case 500: $result="同花大顺"; break;
                case 1000: $result="五鬼"; break;
                case -1: $result="四张同花"; break;
                case -2: $result="四张顺"; break;
                case -4: $result="乌龙"; break;
                case -5: $result="乌龙"; break;
                default:$result=""; break;

            }
        }elseif ($game_type == \Yii::$app->params['att']) {
            switch($first_win_type){
                case -3: $result="小一对"; break;
                case 1: $result="一对"; break;
                case 2: $result="两对"; break;
                case 3: $result="三条"; break;

                case 5: $result="顺子"; break;
                case 7: $result="同花"; break;
                case 10: $result="葫芦"; break;
                case 40:
                case 50:
                case 60: $result="小四梅"; break;
                case 100:
                case 120:
                case 140: $result="同花小顺"; break;
                case 150:
                case 200:
                case 250: $result="同花大顺"; break;
                case 300:
                case 400:
                case 500: $result="五梅"; break;
                case -1: $result="四张同花"; break;
                case -2: $result="四张顺"; break;
                case -4: $result="乌龙"; break;
                case -5: $result="乌龙"; break;
                default:$result=""; break;

            }
        }elseif ($game_type == \Yii::$app->params['big_plate']) {
            switch($first_win_type){
                case -3: $result="小一对"; break;
                case 1: $result="一对"; break;
                case 2: $result="两对"; break;
                case 3: $result="三条"; break;
                case 5: $result="顺子"; break;
                case 7: $result="同花"; break;
                case 15: $result="葫芦"; break;
                case 65: $result="四梅"; break;
                case 150: $result="同花小顺"; break;
                case 250: $result="同花大顺"; break;
                case 350: $result="五梅"; break;
                case -1: $result="四张同花"; break;
                case -2: $result="四张顺"; break;
                case -4: $result="乌龙"; break;
                case -5: $result="乌龙"; break;
                default:$result=""; break;

            }
        }else{
            $result = null;
        }
        return $result;
    }
}