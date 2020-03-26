<?php
namespace backend\models;

use common\models\DataGameListInfo;
use common\services\ToolService;
use Yii;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use backend\services\SystemModuleService;
use backend\services\AdminModuleService;
class BackendUser extends ActiveRecord implements IdentityInterface
{

    const STATUS_DELETED = 0;

    const STATUS_ACTIVE = 10;

    private $_menus;

    private $rightUrls;

    /**
     */
    public static function validatePassword($user, $password)
    {
        return ($user != null && Yii::$app->getSecurity()->validatePassword($password, $user->password));
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public static function login($username, $password)
    {
        $user = AdminUser::findByUsername($username);
        if (Yii::$app->user->login($user, 60 * 60) == true) {
            $user->setPasswordSession($password);
            $user->setAccessToken();//设置访问令牌
            $user->initUserModuleList();
            $user->initUserUrls();
            $user->SetLoginLog();//设置登录日志
            return true;
        }
        return false;
    }

    /**
     * Finds user by username
     *
     * @param string $username            
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return AdminUser::find()->filterWhere([
            'uname' => $username,
        ])->one();
    }

    public static function findIdentity($id)
    {
        return self::findOne([
            'id' => $id,
        ]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * cookie
     *
     * @see \yii\web\IdentityInterface::getId()
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * cookie登录需要实现
     *
     * @see \yii\web\IdentityInterface::getAuthKey()
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * cookie登录需要实现
     *
     * @see \yii\web\IdentityInterface::getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }



  
    
    
        public function initUserModuleList()
        {
            $adminModuleService = new AdminModuleService();
            $urlList = $adminModuleService->getUserModuleList($this->id);
            $games = DataGameListInfo::find()->andFilterWhere(['game_switch'=>0])->andFilterWhere(['<>','game_number',0])->all();
            $game_results = [0];
            foreach ($games as $game){
                $game_results[] = $game->game_number;
            }
            $menus = array();
            $count = 0;
            $count1 = 0;
            $length = count($urlList) - 1;

            foreach ($urlList as $key => $_url) {
                if(in_array($_url['game'],$game_results)) {
                    if ($key === 0) {
                        $menus[$count]["id"] = $_url["mid"];
                        $menus[$count]["label"] = $_url["mlb"];
                        $menus[$count]["url"] = $_url["furl"];
                        $funcList = array();
                        $count1 = 0;
                        $funcList[$count1]["id"] = $_url["fid"];
                        $funcList[$count1]["label"] = $_url["flb"];
                        $funcList[$count1]["url"] = $_url["furl"];

                        $rightList = array();
                        $count11 = 0;
                    } else if ($menus[$count]["id"] !== $_url["mid"]) {
                        $funcList[$count1]["rightList"] = $rightList;
                        $menus[$count]["funcList"] = $funcList;
                        $count++;
                        $menus[$count]["id"] = $_url["mid"];
                        $menus[$count]["label"] = $_url["mlb"];
                        $menus[$count]["url"] = $_url["furl"];
                        $funcList = array();
                        $count1 = 0;
                        $funcList[$count1]["id"] = $_url["fid"];
                        $funcList[$count1]["label"] = $_url["flb"];
                        $funcList[$count1]["url"] = $_url["furl"];

                        $rightList = array();
                        $count11 = 0;
                    } else {
                        if ($funcList[$count1]["id"] !== $_url["fid"]) {
                            $funcList[$count1]["rightList"] = $rightList;
                            $count1++;
                            $funcList[$count1]["id"] = $_url["fid"];
                            $funcList[$count1]["label"] = $_url["flb"];
                            $funcList[$count1]["url"] = $_url["furl"];

                            $rightList = array();
                            $count11 = 0;
                        } else {
                            if ($funcList[$count1]["id"] !== $_url["fid"]) {
                                $funcList[$count1]["rightList"] = $rightList;
                                $count1 ++;
                                $funcList[$count1]["id"] = $_url["fid"];
                                $funcList[$count1]["label"] = $_url["flb"];
                                $funcList[$count1]["url"] = $_url["furl"];

                                $rightList = array();
                                $count11 = 0;
                            }
                        }
                    }

                    $rightList[$count11]["id"] = $_url["rid"];
                    $rightList[$count11]["label"] = $_url["rlb"];
                    $rightList[$count11]["module"] = $_url["url"];
                    $rightList[$count11]["controller"] = $_url["para_name"];
                    $rightList[$count11]["action"] = $_url["para_value"];
                    $count11 ++;

                    if ($key === $length) {
                        $funcList[$count1]["rightList"] = $rightList;
                        $menus[$count]["funcList"] = $funcList;
                    }
                }
            }
                $this->_menus = $menus;
                Yii::$app->session['system_menus_'.$this->id] = $menus;
                return $menus;
        }

        public function initUserUrls($userId = 0)
        {
            $adminModuleService = new AdminModuleService();
            $rightUrls = $adminModuleService->getUserUrls($this->id);
            $funcs = $adminModuleService->getAllFunctions();
            $funcData = [];
            foreach($funcs as $fun){
                $funcData[$fun['right_id']] = $fun;
            }
            $rightData = [];
            foreach($rightUrls as $url){
                $right_id = $url['right_id'];
                if(isset($funcData[$right_id])){
                    $fun = $funcData[$right_id];
                    $url['right_name'] = $fun['right_name'];
                    $url['entry_url'] = $fun['entry_url'];
                    $url['menu_name'] = $fun['menu_name'];
                    $url['module_name'] = $fun['display_label'];
                    $rightData[$url['para_name'].'/'.$url['para_value']] = $url;
                }
    
            }
            Yii::$app->session['system_rights_'.$this->id] = $rightData;
            return $rightData;
        }

    

//     public function initUserModuleList()
//     {
//         $systemModuleService = new SystemModuleService();
//         $urlList = $systemModuleService->getUserModuleList($this->id);
//         $menus = array();
//         $count = 0;
//         $count1 = 0;
//         $length = count($urlList) - 1;
//         foreach ($urlList as $key => $_url) {
//             if ($key === 0) {
//                 $menus[$count]["id"] = $_url["mid"];
//                 $menus[$count]["label"] = $_url["mlb"];
//                 $menus[$count]["url"] = $_url["furl"];
//                 $funcList = array();
//                 $count1 = 0;
//                 $funcList[$count1]["id"] = $_url["fid"];
//                 $funcList[$count1]["label"] = $_url["flb"];
//                 $funcList[$count1]["url"] = $_url["furl"];
    
//                 $rightList = array();
//                 $count11 = 0;
//             } else
//                 if ($menus[$count]["id"] !== $_url["mid"]) {
//                     $funcList[$count1]["rightList"] = $rightList;
//                     $menus[$count]["funcList"] = $funcList;
//                     $count ++;
//                     $menus[$count]["id"] = $_url["mid"];
//                     $menus[$count]["label"] = $_url["mlb"];
//                     $menus[$count]["url"] = $_url["furl"];
//                     $funcList = array();
//                     $count1 = 0;
//                     $funcList[$count1]["id"] = $_url["fid"];
//                     $funcList[$count1]["label"] = $_url["flb"];
//                     $funcList[$count1]["url"] = $_url["furl"];
    
//                     $rightList = array();
//                     $count11 = 0;
//                 } else {
//                     if ($funcList[$count1]["id"] !== $_url["fid"]) {
//                         $funcList[$count1]["rightList"] = $rightList;
//                         $count1 ++;
//                         $funcList[$count1]["id"] = $_url["fid"];
//                         $funcList[$count1]["label"] = $_url["flb"];
//                         $funcList[$count1]["url"] = $_url["furl"];
    
//                         $rightList = array();
//                         $count11 = 0;
//                     }
//                 }
//                 $rightList[$count11]["id"] = $_url["rid"];
//                 $rightList[$count11]["label"] = $_url["rlb"];
//                 $rightList[$count11]["module"] = $_url["url"];
//                 $rightList[$count11]["controller"] = $_url["para_name"];
//                 $rightList[$count11]["action"] = $_url["para_value"];
//                 $count11 ++;
    
//                 if ($key === $length) {
//                     $funcList[$count1]["rightList"] = $rightList;
//                     $menus[$count]["funcList"] = $funcList;
//                 }
//                 //             break;
//         }
//         $this->_menus = $menus;
//         Yii::$app->session['system_menus_'.$this->id] = $menus;
//         return $menus;
//     }
    
//     public function initUserUrls($userId = 0)
//     {
//         $systemModuleService = new SystemModuleService();
//         $rightUrls = $systemModuleService->getUserUrls($this->id);
//         $funcs = $systemModuleService->getAllFunctions();
//         $funcData = [];
//         foreach($funcs as $fun){
//             $funcData[$fun['right_id']] = $fun;
//         }
//         $rightData = [];
//         foreach($rightUrls as $url){
//             $right_id = $url['right_id'];
//             if(isset($funcData[$right_id])){
//                 $fun = $funcData[$right_id];
//                 $url['right_name'] = $fun['right_name'];
//                 $url['entry_url'] = $fun['entry_url'];
//                 $url['func_name'] = $fun['func_name'];
//                 $url['module_name'] = $fun['display_label'];
//                 $rightData[$url['para_name'].'/'.$url['para_value']] = $url;
//             }
            
//         }
//         Yii::$app->session['system_rights_'.$this->id] = $rightData;
//         return $rightData;
//     }

    

    public function getSystemMenus(){
        return $this->initUserModuleList();
    }

    public function getSystemRights(){
         return $this->initUserUrls();
    }
    
    public function clearUserSession(){
        Yii::$app->session['system_menus_'.$this->id] = null;
        Yii::$app->session['system_rights_'.$this->id] = null;
    }
}

?>