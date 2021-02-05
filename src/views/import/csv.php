<?php

use import\models\ImportCsv;
use import\models\ImportCsvSearch;
use kartik\widgets\Select2;
use xlerr\common\widgets\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model ImportService */

$this->title = '数据导入';

$this->params['breadcrumbs'][] = ['label' => '文件列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $this->title ?></h3>
            </div>

            <?php $form = ActiveForm::begin([
                'action' => [''],
                'type'   => ActiveForm::TYPE_HORIZONTAL,
            ]); ?>

            <div class="box-body">

                <?= $form->field($model, 'type')->widget(Select2::class, [
                    'data'          => ImportCsv::typeList(),
                    'hideSearch'    => false,
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                    'options'       => [
                        'prompt' => $model->getAttributeLabel('type'),
                    ],
                ]) ?>

                <?= $form->field($model, 'file')->fileInput() ?>

            </div>

            <div class="box-footer">
                <div class="col-md-offset-2">
                    <?= Html::submitButton('导入', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
    <div class="col-md-6">
        <pre id="view">

        </pre>
    </div>
</div>

<style>
    .btn-group > label.btn.active {
        background-color: #3c8dbc;
        border-color: #367fa9;
        color: #ffffff;
    }
</style>
<script>
    <?php $this->beginBlock('main') ?>
    const config = <?= json_encode(ImportCsv::config()) ?>;
    $('#importcsv-type').on('change', function () {
        const index = $(this).val();
        $('#view').text(JSON.stringify(config[index], null, "    "));
    }).trigger('change');
    <?php $this->endBlock() ?>
    <?php $this->registerJs($this->blocks['main']) ?>
</script>
