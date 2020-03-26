<?php
namespace common\models;

use Yii;

/**
 * This is the model class for table "backend_version".
 *
 * @property integer $id
 * @property string $version
 * @property string $name
 * @property string $update_time
 */
class BackendVersion extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_version';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['update_time'], 'safe'],
            [['version', 'name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'version' => '版本号',
            'name' => '平台名',
            'update_time' => '更新时间',
        ];
    }

    /**
     *   版本更新
     */
    public function VersionPlus()
    {
        $obj = self::find()->one();
        $versionArr = explode('.',$obj->version);
        $versionStr = implode($versionArr,"");
        $newVersion = $versionStr+1;
        $newVersion = str_split($newVersion);
        $obj->version = implode($newVersion,".");
        $obj->save();
        return $obj->version;
    }

}
