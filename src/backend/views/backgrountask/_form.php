<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model magicalella\backgroundtask\Backgroundtask */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="task-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'stato')->textInput() ?>

    <?= $form->field($model, 'action')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'params')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'id_user')->textInput() ?>

    <?= $form->field($model, 'progress')->textInput() ?>

    <?= $form->field($model, 'output')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'log')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
