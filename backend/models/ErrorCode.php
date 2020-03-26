<?php
namespace  backend\models;

use Yii;
use yii\db\Exception;

/**
 *  这个所有的错误信息显示
 */
class ErrorCode
{
    const ERROR_OK                     = 200;
    const ERROR_SYSTEM                 = 1;
    const ERROR_PARAM                  = 2;
    const ERROR_AWAIT                  = 3;
    const ERROR_RELOGIN                = 4;
    const ERROR_YZM                    = 5;
    const ERROR_OBJ                    = 6;
    const ERROR_REDIS_KEY              = 7;
    const ERROR_DATABASE_CONFIG        = 8;
    const ERROR_GAME                   = 9;

    const ERROR_ACCOUNT_EXIST          = 11;
    const ERROR_PASSWORD               = 12;
    const ERROR_ACCOUNT_NOT_EXIST      = 13;
    const ERROR_ACCOUNT_MENU_EXIST     = 14;
    const ERROR_ACCOUNT_FUN_EXIST      = 15;
    const ERROR_ACCOUNT_FUN_NOT_ACCESS = 16;
    const ERROR_ACCOUNT_FUN_NOT_EXIST  = 17;
    const ERROR_ACCOUNT_LOGIN_LOCK     = 18;
    const ERROR_ACCOUNT_LOGIN_BIND     = 19;
    const ERROR_ACCOUNT_IS_BIND        = 20;

    const ERROR_PAGE_UNKNOWN           = 21;
    const ERROR_ACCOUNT_HAS_SON        = 22;
    const ERROR_ACCOUNT_NAME_EXIST     = 23;
    const ERROR_ACCOUNT_HAS_DIAMOND    = 24;
    const ERROR_ACCOUNT_RE_LOGIN       = 25;
    const ERROR_ROLE_NOT_EXIST         = 31;
    const ERROR_MENU_NOT_EXIST         = 41;

    const ERROR_NOT_EACH_PARENT       = 51;
    const ERROR_NOT_SON               = 52;
    const ERROR_UPDATE_FUN_NOT_SON    = 53;
    const ERROR_UPDATE_MENU_NOT_SON   = 54;
    const ERROR_NOT_CREATE_ROLE_ACCOUNT = 55;

    const ERROR_POP_CODE              = 61;
    const ERROR_POP_CODE_EXIST        = 62;
    const ERROR_DLS_POP_CODE_NOT_EXIST= 63;


    const ERROR_GAME_NO_OPEN         = 1001;
    const ERROR_GAME_CREATE_MACHINE  = 1002;
    const ERROR_GAME_MACHINE_NOT_EXIST = 1003;
    const ERROR_GAME_MACHINE_HAS_ONE  = 1004;

    const ERROR_CURL                 = 1011;
    const ERROR_CURL_STATUS          = 1012;
    const ERROR_DATA_NOT_UP          = 1013;
    const ERROR_MACHINE_RESERVATION  = 1021;

    const ERROR_GAME_NOT_EXIST       = 1101;
    const ERROR_USER_NOT_EXIST       = 1102;
    const ERROR_USER_PWD_FORMAT      = 1103;
    const ERROR_USER_IS_GAMING       = 1104;
    const ERROR_FG_GAMING            = 1105;

    const ERROR_DIAMOND_NUM          = 1201;
    const ERROR_DIAMOND_NUM_MINUS    = 1202;
    const ERROR_DIAMOND_NUM_MINUS_DL = 1206;
    const ERROR_DIAMOND_MUST_AGENT   = 1203;
    const ERROR_DIAMOND_IS_ONLINE    = 1204;
    const ERROR_DIAMOND_NOT_SON      = 1205;
    const ERROR_DIAMOND_NOT_EXCEED     = 1207;
    const ERROR_DIAMOND_NOT_LOW_EXCEED = 1208;

    const ERROR_SCORE_NUM_MINUS     = 1212;

    const ERROR_NOT_USER             = 1301;

    const ERROR_USER_IS_ATTENTION   = 1311;

    const ERROR_NOT_AI_ACCOUNT      = 1411;

    const ERROR_HORSE_RATE_RANGE_PARAM = 1402;

    const ERROR_NOT_CREATE_DIR      = 1511;
    const ERROR_NOT_CREATE_FILE     = 1512;

    const ERROR_DIAMOND_FREQUENCE_LIMIT = 1601;

    const ERROR_NOT_DEFAULT_ODDS = 1701;

    private $errorList = [
        '200'=> "正常",
        '1'  => "系统错误",
        '2'  => "参数错误",
        '3'  => "您的账号长时间未操作,请重新登陆",
        '4'  => "您的账号长时间未操作,请重新登陆",
        '5'  => "验证码不正确",
        '6'  => "请求未知对象",
        '7'  => "错误的Redis_KEY",
        '8'  => "数据库配置错误",
        '9'  => "未知游戏",

        '11' => "账号已存在",
        '12' => "账号密码不正确",
        '13' => "账号不存在",
        '14' => "账号菜单权限已存在",
        '15' => "账号功能权限已存在",
        '16' => "账号没有权限使用这个功能",
        '17' => "访问后台配置不存在的功能",
        '18' => "账号已被锁定,请联系管理员解锁",
        '19' => "您的账号异常,请联系管理员",
        '20' => "账号已绑定",
        '21' => "请求未知页",
        '22' => "该账号存在下级账户不能删除",
        '23' => "员工名称已存在",
        '24' => "该账号有钻石不能删除",
        '25' => "网络异常,请重新登陆",
        '31' => "角色不存在",
        '41' => "菜单不存在",
        '51' => "不能相互为上下级",
        '52' => "只能操作您的下级账户",
        '53' => "只能操作您的下级账户权限",
        '54' => "只能操作您的下级账户菜单",
        '55' => "您没有权限创建这个角色的账号",
        '61' => "不存在此推广码",
        '62' => "推广码已存在",
        '63' => "不存在此代理商",

        '1001' => "没有状态为开启的游戏",
        '1002' => "创建机台失败",
        '1003' => "机台不存在",
        '1004' => "房间至少要有一个机台",
        '1011' => "通讯错误",
        '1012' => "通讯请求返回状态错误",
        '1013' => "没有任何修改",
        '1021' => "该机台留机状态正常",

        '1101' => "游戏不存在",
        '1102' => "玩家不存在",
        '1103' => "密码格式不正确",
        '1104' => "玩家正在游戏",
        '1105' => "FG游戏中不能开洗分",

        '1201' => "错误的钻石数量",
        '1202' => "钻石数量不能为负",
        '1206' => "代理钻石数量不能为负",
        '1203' => "管理员只能给总代理加钻",
        '1204' => "玩家在线的时候不能下钻",
        '1205' => "只能操作自己下级玩家钻石",
        '1207' => "分成不能超过上级",
        '1208' => "分成不能低于下级",
        '1212' => "分数不能为负",

        '1301' => "玩家不存在",

        '1311' => "玩家已关注",


        '1402' => '随机几率 至少有一个参数',//黄金赛马 随机几率参数错误

        '1411' => "该账号不在账号表里面,不能设置",

        '1511' => "创建文件夹失败",
        '1512' => "创建文件失败",

        '1601' => "操作频率过高,请稍后操作",

        '1701' => "默认机率不存在",
    ];


    /**
     * 返回错误信息
     * @param $code
     * @return string
     */
    public function getMessage($code)
    {
        return $this->errorList[$code];
    }
}
