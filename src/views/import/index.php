<?php

use import\models\ImportCsvSearch;
use import\models\ImportCsv;
use xlerr\common\widgets\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\View;

/* @var $this View */
/* @var $searchModel PaymentCsvSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title                   = Yii::t('import', '文件列表');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_search', ['model' => $searchModel]); ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns'      => [
        [
            'class'          => 'yii\grid\ActionColumn',
            'template'       => '{delete} {update} {download} {clean}',
            'buttons'        => [
                'delete'   => function ($url, ImportCsvSearch $model) {
                    return Html::a('删除', $url, [
                        'class' => 'btn btn-xs btn-danger',
                        'title' => '删除',
                        'data'  => [
                            'confirm' => '是否确定删除?',
                            'id'      => $model->id,
                        ],
                    ]);
                },
                'update'   => function ($url, ImportCsvSearch $model) {
                    return Html::a('重新导入', $url, [
                        'class' => 'btn btn-xs btn-primary',
                        'title' => '重新导入',
                        'data'  => [
                            'confirm' => '是否确定重新导入?',
                            'id'      => $model->id,
                        ],
                    ]);
                },
                'download' => function ($url) {
                    return Html::a('下载', $url, [
                        'class'          => 'btn btn-xs btn-info',
                        'title'          => '下载',
                        'target_browser' => true,
                    ]);
                },
                'clean'    => function ($url) {
                    return Html::a('清理', $url, [
                        'class' => 'btn btn-xs btn-success',
                        'title' => '清理',
                        'data'  => [
                            'confirm' => '确定要清理已导入的数据吗?',
                        ],
                    ]);
                },
            ],
            'visibleButtons' => [
                'delete' => function (ImportCsvSearch $model) {
                    return in_array($model->status, [
                        ImportCsvSearch::SUCCESS_STATUS,
                        ImportCsvSearch::FAIL_STATUS,
                    ], true);
                },
                'update' => function (ImportCsvSearch $model) {
                    return in_array($model->status, [
                        ImportCsvSearch::SUCCESS_STATUS,
                        ImportCsvSearch::FAIL_STATUS,
                    ], true);
                },
                'clean'  => function (ImportCsvSearch $model) {
                    return in_array($model->status, [
                        ImportCsvSearch::SUCCESS_STATUS,
                        ImportCsvSearch::FAIL_STATUS,
                    ], true);
                },
            ],
        ],
        [
            'attribute' => 'type',
            'format'    => ['in', ImportCsvSearch::typeList()],
        ],
        [
            'attribute' => 'file',
        ],
        [
            'attribute' => 'success_line',
        ],
        [
            'attribute' => 'status',
            'format'    => ['in', ImportCsvSearch::statusList()],
        ],
        [
            'attribute' => 'error_message',
            'format'    => 'raw',
            'value'     => static function (ImportCsvSearch $model) {
                if ($model->status === ImportCsv::SUCCESS_STATUS) {
                    return '';
                }

                return Html::tag('span', StringHelper::truncate($model->error_message, 30), [
                    'title' => $model->error_message,
                ]);
            },
        ],
        'creator.username',
        [
            'attribute' => 'create_at',
        ],
        [
            'attribute' => 'update_at',
        ],
    ],
]); ?>
