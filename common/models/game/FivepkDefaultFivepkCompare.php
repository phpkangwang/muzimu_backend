<?php
namespace common\models\game;

use Yii;

/**
 * This is the model class for table "fivepk_default_fivepk_compare".
 *
 * @property integer $id
 * @property string $fivepk_default_fivepk_id
 * @property integer $compare_bet_win
 * @property integer $compare_bet
 * @property integer $big_small
 * @property integer $compare_card
 * @property string $last_time
 */
class FivepkDefaultFivepkCompare extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_default_fivepk_compare';
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
            [['fivepk_default_fivepk_id', 'compare_bet_win', 'compare_bet', 'big_small', 'compare_card', 'last_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fivepk_default_fivepk_id' => '半比平比双比win分数',
            'compare_bet_win' => '半比平比双比win分数',
            'compare_bet' => '0续玩1-半比2-平比3-双比',
            'big_small' => '0-大 1-小',
            'compare_card' => '大小牌型2-A && 历史牌型',
            'last_time' => 'Last Time',
        ];
    }

    public function getCompare_bet($compare_bet){
        switch ($compare_bet){
            case 0:
                return "续玩";
            case 1:
                return "半比";
            case 2:
                return "平比";
            case 3:
                return "双比";
            default:
                return "";
        }
    }

    public function getbig_small($big_small){
        switch ($big_small){
            case 0:
                return "大";
            case 1:
                return "小";
            default:
                return "";
        }
    }

    public function getCompareByFivepkId($fivepkIdArr){
        return self::find()->andWhere(['in','fivepk_default_fivepk_id',$fivepkIdArr])->asArray()->all();
    }

    public function finds($ids)
    {
        return self::find()->where(['in','id',$ids])->asArray()->all();
    }

  /**
     * 返回数据库字段信息，仅在生成CRUD时使用，如不需要生成CRUD，请注释或删除该getTableColumnInfo()代码
     * COLUMN_COMMENT可用key如下:
     * label - 显示的label
     * inputType 控件类型, 暂时只支持text,hidden  // select,checkbox,radio,file,password,
     * isEdit   是否允许编辑，如果允许编辑将在添加和修改时输入
     * isSearch 是否允许搜索
     * isDisplay 是否在列表中显示
     * isOrder 是否排序
     * udc - udc code，inputtype为select,checkbox,radio三个值时用到。
     * 特别字段：
     * id：主键。必须含有主键，统一都是id
     * create_date: 创建时间。生成的代码自动赋值
     * update_date: 修改时间。生成的代码自动赋值
     */
    public function getTableColumnInfo(){
        return array(
        'id' => array(
                        'name' => 'id',
                        'allowNull' => false,
//                         'autoIncrement' => true,
//                         'comment' => '',
//                         'dbType' => "int(20)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => true,
                        'phpType' => 'integer',
                        'precision' => '20',
                        'scale' => '',
                        'size' => '20',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('id'),
                        'inputType' => 'hidden',
                        'isEdit' => true,
                        'isSearch' => true,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'fivepk_default_fivepk_id' => array(
                        'name' => 'fivepk_default_fivepk_id',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '半比平比双比win分数',
//                         'dbType' => "bigint(20)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '20',
                        'scale' => '',
                        'size' => '20',
                        'type' => 'bigint',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('fivepk_default_fivepk_id'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'compare_bet_win' => array(
                        'name' => 'compare_bet_win',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '半比平比双比win分数',
//                         'dbType' => "int(20)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '20',
                        'scale' => '',
                        'size' => '20',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('compare_bet_win'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'compare_bet' => array(
                        'name' => 'compare_bet',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '1-半比2-平比3-双比',
//                         'dbType' => "int(10)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '10',
                        'scale' => '',
                        'size' => '10',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('compare_bet'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'big_small' => array(
                        'name' => 'big_small',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '0-大 1-小',
//                         'dbType' => "int(10)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '10',
                        'scale' => '',
                        'size' => '10',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('big_small'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'compare_card' => array(
                        'name' => 'compare_card',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '大小牌型2-A && 历史牌型',
//                         'dbType' => "int(10)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '10',
                        'scale' => '',
                        'size' => '10',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('compare_card'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'last_time' => array(
                        'name' => 'last_time',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "bigint(20)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '20',
                        'scale' => '',
                        'size' => '20',
                        'type' => 'bigint',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('last_time'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		        );
        
    }
 
}
