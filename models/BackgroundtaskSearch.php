<?php
/*
* @author    Raffaella Lollini <raffaella@kattivamente.it>
* @copyright 2023 Raffaella Lollini
*/
namespace magicalella\backgroundtask\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use magicalella\backgroundtask\models\Backgroundtask;

/**
 * ExporttaskSearch represents the model behind the search form of `common\models\backgroundtask`.
 */
class BackgroundtaskSearch extends Backgroundtask
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'stato', 'id_user', 'progress'], 'integer'],
            [['action', 'params', 'output', 'log'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = Backgroundtask::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'stato' => $this->stato,
            'id_user' => $this->id_user,
            'progress' => $this->progress,
        ]);

        $query->andFilterWhere(['like', 'action', $this->action])
            ->andFilterWhere(['like', 'params', $this->params])
            ->andFilterWhere(['like', 'output', $this->output])
            ->andFilterWhere(['like', 'log', $this->log]);

        return $dataProvider;
    }
}
