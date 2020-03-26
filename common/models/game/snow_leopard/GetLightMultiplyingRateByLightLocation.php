<?php


namespace common\models\game\snow_leopard;


class GetLightMultiplyingRateByLightLocation
{

    //根据位置获取倍率

    public static function getLightMultiplyingRate($lightLocation, $lightMultiplyingMark = 1)
    {
        $lightMultiplyingRate = 0;

        switch ($lightLocation) {
            // 苹果位置:苹果统一五倍
            case 3 :
                $lightMultiplyingRate = 5;
                break;
            case 8 :
                $lightMultiplyingRate = 5;
                break;
            case 14 :
                $lightMultiplyingRate = 5;
                break;
            case 20 :
                $lightMultiplyingRate = 5;
                break;
            // 统一两倍
            case 9 :
                $lightMultiplyingRate = 2;
                break;
            case 15 :
                $lightMultiplyingRate = 2;
                break;
            case 21 :
                $lightMultiplyingRate = 2;
                break;
            case 6 :
                $lightMultiplyingRate = 2;
                break;
            case 18 :
                $lightMultiplyingRate = 2;
                break;
            case 12 :
                $lightMultiplyingRate = 2;

                break;
            // 取小倍率
            case 10 :
                $lightMultiplyingRate = $lightMultiplyingMark * 5;
                break;
            case 22 :
                $lightMultiplyingRate = $lightMultiplyingMark * 5;
                break;
            case 4 :
                $lightMultiplyingRate = $lightMultiplyingMark * 5;
                break;
            case 16 :
                $lightMultiplyingRate = $lightMultiplyingMark * 5;
                break;
            case 11 :
                $lightMultiplyingRate = $lightMultiplyingMark * 5;
                break;
            case 23 :
                $lightMultiplyingRate = $lightMultiplyingMark * 5;
                break;
            // 取大倍率
            case 5 :
                $lightMultiplyingRate = 2 * 10;
                break;
            case 17 :
                $lightMultiplyingRate = 2 * 10;
                break;
            case 13 :
                $lightMultiplyingRate = 2 * 10;
                break;
            case 1 :
                $lightMultiplyingRate = 2 * 10;
                break;
            case 24 :
                $lightMultiplyingRate = 2 * 10;
                break;
            case 2 :
                $lightMultiplyingRate = 2 * 10;
                break;
            // 火车统一取0倍 功能辅助灯型
            case 7 :
                $lightMultiplyingRate = 0;
                break;
            case 19 :
                $lightMultiplyingRate = 0;
                break;
            default :
                $lightMultiplyingRate = 0;
                break;
        }

        return $lightMultiplyingRate;
    }


}