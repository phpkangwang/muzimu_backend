<?php
namespace common\models\game;

use Yii;
use backend\models\ErrorCode;
use backend\models\MyException;

/**
 * This is the model class for table "fivepk_diamond".
 *
 * @property integer $id
 * @property string $seoid
 * @property integer $diamond_up
 * @property integer $diamond_before
 * @property integer $diamond_after
 * @property string $diamond_type
 * @property string $operator_time
 * @property string $operator
 * @property integer $diamond_down
 */
class FivepkDiamond extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_diamond';
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
            [['diamond_up', 'diamond_before', 'diamond_after', 'operator_time', 'diamond_down'], 'integer','min'=>0],
            [['diamond_up','diamond_down'],'validateNUM'],
            [['seoid'], 'string', 'max' => 10],
            [['diamond_type'], 'string', 'max' => 200],
            [['operator'], 'string', 'max' => 255]
        ];
    }


    public function validateNUM($attribute)
    {
        if($this->$attribute %10 != 0){
            $this->addError($attribute,'必须是10的倍数');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'seoid' => '代理商',
            'diamond_up' => '上钻数',
            'diamond_before' => '上钻前钻石数',
            'diamond_after' => '上钻后钻石数',
            'diamond_type' => '备注',
            'operator_time' => '操作时间',
            'operator' => '操作员',
            'diamond_down' => '下钻数',
        ];
    }

    /**
     * 添加修改
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            if( $this->save() )
            {
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 分页
     * @return array
     */
    public function Page($pageNo, $pageSize, $where)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->where($where)->offset($offset)->limit($limit)->orderBy('id desc')->asArray()->all();
    }

    /**
     *  获取最大条数
     */
    public function Count($where)
    {
        return self::find()->where($where)->count();
    }

    /**
     *   一段时间内营业额
     */
    public function getSum($where){
        $objs = self::find()->where($where)->asArray()->all();
        $data['operatrTimes']   = count($objs);
        $upDiamonds             = array_column($objs,'diamond_up');
        $data['sumUpDiamond']   = array_sum($upDiamonds);
        $downDiamonds           = array_column($objs,'diamond_down');
        $data['sumDowmDiamond'] = array_sum($downDiamonds);
        $data['result'] = $data['sumUpDiamond'] - $data['sumDowmDiamond'];
        return $data;
    }




}
