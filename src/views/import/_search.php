<?php

use import\models\ImportCsv;
use import\models\ImportCsvSearch;
use xlerr\common\widgets\ActiveForm;
use xlerr\common\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use kartik\widgets\DateTimePicker;

/* @var $this View */
/* @var $model ImportCsvSearch */
?>

<div class="box box-default search">
    <div class="box-header with-border">
        <i class="glyphicon glyphicon-search"></i>
        <h3 class="box-title"><?= Yii::t('import', 'Search') ?></h3>
<div class="box-tools pull-right">
    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
</div>
</div>

<div class="box-body">

    <?php $form = ActiveForm::begin([
        'action'        => ['index'],
        'method'        => 'get',
        'type'          => ActiveForm::TYPE_INLINE,
        'waitingPrompt' => ActiveForm::WAITING_PROMPT_SEARCH,
    ]); ?>

    <?= $form->field($model, 'startDate')->widget(DateTimePicker::class, [
        'options'       => [
            'placeholder' => '创建开始时间',
        ],
        'type'          => DateTimePicker::TYPE_INPUT,
        'pluginOptions' => [
            'minView'        => 'month',
            'todayBtn'       => true,
            'todayHighlight' => true,
            'autoclose'      => true,
            'format'         => 'yyyy-mm-dd',
        ],
    ]) ?>
    <?= $form->field($model, 'endDate')->widget(DateTimePicker::class, [
        'options'       => [
            'placeholder' => '创建结束时间',
        ],
        'type'          => DateTimePicker::TYPE_INPUT,
        'pluginOptions' => [
            'minView'        => 'month',
            'todayBtn'       => true,
            'todayHighlight' => true,
            'autoclose'      => true,
            'format'         => 'yyyy-mm-dd',
        ],
    ]) ?>
    <?= $form->field($model, 'status')->widget(Select2::class, [
        'data'          => ImportCsv::statusList(),
        'hideSearch'    => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
        'options'       => [
            'placeholder' => $model->getAttributeLabel('status'),
        ],
    ]) ?>
    <?= Html::submitButton(Yii::t('import', 'Search'), ['class' => 'btn btn-primary']) ?>
    <?= Html::a(Yii::t('import', 'Reset'), ['index'], ['class' => 'btn btn-default']) ?>
    <?= Html::a(Yii::t('import', 'Create Import Csv'), ['csv'], ['class' => 'btn btn-success']) ?>

    <?php ActiveForm::end(); ?>

</div>
</div>


