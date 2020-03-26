<?php

namespace backend\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\game\FivepkMonthContribution;

/**
 * FivepkMonthContributionSearch represents the model behind the search form about `Hass\Compass\Models\fivepk\FivepkMonthContribution`.
 */
class FivepkMonthContributionSearch extends FivepkMonthContribution
{
    public $seoid;
    public $nick_name;
    public $name;
    public $seo_ids;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'account_id', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12'], 'integer'],
            [['seoid','nick_name','name'],'string'],
            [['record_time','seo_ids'], 'safe'],
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
        $query = FivepkMonthContribution::find()
                ->joinWith('fivepkAccount')
                ->joinWith('fivepkPlayerInfo');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'fivepk_month_contribution.id' => $this->id,
            'fivepk_month_contribution.account_id' => $this->account_id,
            'fivepk_month_contribution.m1' => $this->m1,
            'fivepk_month_contribution.m2' => $this->m2,
            'fivepk_month_contribution.m3' => $this->m3,
            'fivepk_month_contribution.m4' => $this->m4,
            'fivepk_month_contribution.m5' => $this->m5,
            'fivepk_month_contribution.m6' => $this->m6,
            'fivepk_month_contribution.m7' => $this->m7,
            'fivepk_month_contribution.m8' => $this->m8,
            'fivepk_month_contribution.m9' => $this->m9,
            'fivepk_month_contribution.m10' => $this->m10,
            'fivepk_month_contribution.m11' => $this->m11,
            'fivepk_month_contribution.m12' => $this->m12,
        ]);

        $query->andFilterWhere(['like', 'record_time', $this->record_time])
            ->andFilterWhere(['in','fivepk_account.seoid',$this->seo_ids])
            ->andFilterWhere(['like','fivepk_player_info.nick_name',$this->nick_name])
            ->andFilterWhere(['like','fivepk_account.name',$this->name])
            ->andFilterWhere(['like','fivepk_account.seoid',$this->seoid]);

        return $dataProvider;
    }
}
