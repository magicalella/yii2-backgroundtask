<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use magicalella\backgroundtask\Backgroundtask;

/* @var $this yii\web\View */
/* @var $model magicalella\backgroundtask\Backgroundtask */
/* @var $form yii\widgets\ActiveForm */

$model = new Backgrountask();

?>


    <?php $form = ActiveForm::begin(['action' => ['task/createtask'],
									'options' => ['method' => 'post'],
									'fieldConfig' => [
				                        'template' => "{input}",
				                        'options' => [
				                            'tag'=>'span'
				                        ]
    ]]); ?>

    <?= $form->field($model, 'action')->hiddenInput(['value'=> $action])->label(false);?>

    <?= $form->field($model, 'params')->hiddenInput(['value'=> json_encode($params)])->label(false); ?>

    <?= $form->field($model, 'id_user')->hiddenInput(['value'=> Yii::$app->user->identity->id])->label(false);?>


    <?= Html::submitButton($buttonText, ['class' => 'btn btn-success']) ?>


    <?php ActiveForm::end(); ?>

