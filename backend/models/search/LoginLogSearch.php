<?php

namespace backend\search;

use common\models\AdminLoginLog;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LoginLogSearch represents the model behind the search form about `Hass\compass\Models\db\LoginLog`.
 */
class LoginLogSearch extends AdminLoginLog
{
    //public $user;
    public $start_time;
    public $end_time;
    public $name;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'id_user', 'created_time'], 'integer'],
            [['ip', 'address', 'os', 'device', 'browser','start_time','end_time'], 'safe'],
            [['start_time', 'end_time','name' ], 'string' ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AdminLoginLog::find()->joinWith('user')->orderBy('id DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {

            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'id_user' => $this->id_user,
            'created_time' => $this->created_time,
        ]);
        switch(trim($this->device)){
            case '电脑':
                $this->device = 1;
                $query->andFilterWhere([ 'device'=>$this->device]);
                break;
            case '手机':
                $this->device = 2;
                $query->andFilterWhere([ 'device'=>$this->device]);
                break;
            default:
                $query->andFilterWhere(['like','device',$this->device]);
                break;

        }

        $query->andFilterWhere(['like', 'ip', trim($this->ip)])
            ->andFilterWhere(['like', 'address', trim($this->address)])
            ->andFilterWhere(['like', 'os', trim($this->os)])
            ->andFilterWhere(['like', 'browser', trim($this->browser)])
            ->andFilterWhere(['between', 'created_time', empty($this->start_time)?null:strtotime($this->start_time),empty($this->end_time)?null:strtotime($this->end_time)])
            ->andFilterWhere(['like', 'admin_user.uname', trim($this->name)]);
        //dt($query->createCommand()->getRawSql());
        return $dataProvider;
    }
}
