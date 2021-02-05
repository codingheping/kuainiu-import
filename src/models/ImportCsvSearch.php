<?php

namespace import\models;

use common\models\User;
use yii\data\ActiveDataProvider;

class ImportCsvSearch extends ImportCsv
{
    public $startDate;
    public $endDate;
    public $channel = 'payment';

    public function rules()
    {
        return [
            [['startDate', 'endDate'], 'date', 'format' => 'yyyy-mm-dd'],
            [
                'status',
                'in',
                'range' => [
                    self::SUCCESS_STATUS,
                    self::FAIL_STATUS,
                    self::TODO_STATUS,
                    self::ING_STATUS,
                ],
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'type'          => '类型',
            'create_by'     => '后台人员',
            'domain_url'    => '静态地址',
            'oss_file_name' => 'OSS文件名称',
            'file'          => '文件名称',
            'success_line'  => '导入成功数',
            'error_message' => '错误信息',
            'create_at'     => '创建时间',
            'update_at'     => '更新时间',
            'status'        => '状态',
            'search'        => '搜索',
        ];
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()->where(['business_channel' => $this->channel]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => ['id'],
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['>=', 'create_at', $this->startDate])
            ->andFilterWhere(['<', 'create_at', $this->endDate]);

        return $dataProvider;
    }


    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'create_by']);
    }
}


