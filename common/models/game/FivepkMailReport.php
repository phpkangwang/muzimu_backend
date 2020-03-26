<?php
namespace common\models\game;

use backend\models\BaseModel;
use Yii;

/**
 * This is the model class for table "fivepk_mail_report".
 *
 * @property string $id
 * @property string $account_id
 * @property string $type
 * @property string $title
 * @property string $email_address
 * @property string $content
 * @property integer $is_readed
 * @property string $receive_time
 * @property string $read_time
 * @property string $comment
 */
class FivepkMailReport extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_mail_report';
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
            [['account_id'], 'required'],
            [['account_id', 'is_readed'], 'integer'],
            [['receive_time', 'read_time'], 'safe'],
            [['type', 'title', 'email_address'], 'string', 'max' => 255],
            [['content'], 'string', 'max' => 500],
            [['comment'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => '玩家ID',
            'type' => '报告类型',
            'title' => '报告标题',
            'email_address' => '邮箱地址',
            'content' => '详细内容',
            'is_readed' => '是否已读-0.未读；1.已读',
            'receive_time' => '反馈时间',
            'read_time' => '被读时间',
            'comment' => '备注'
        ];
    }

    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(),['account_id'=>'account_id']);
    }

    public function getAccount()
    {
        return $this->hasOne(FivepkAccount::className(),['account_id'=>'account_id']);
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
