<?php

/**
* @var string $title
* @var string $task
* @var string $class
* @var array $params
* @var string $button_text
*/


use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use magicalella\backgroundtask\Backgroundtask;
$model = new Backgroundtask();
?>
<div class="backgroundtask">
<?php $form = ActiveForm::begin(['action' => ['backgroundtask/backgroundtask/createtask'],
                                'options' => ['method' => 'post'],
                                'fieldConfig' => [
                                    'template' => "{input}",
                                    'options' => [
                                        'tag'=>'span'
                                    ]
]]); ?>

<?= $form->field($model, 'action')->hiddenInput(['value'=> $task])->label(false);?>

<?= $form->field($model, 'params')->hiddenInput(['value'=> json_encode($params)])->label(false); ?>

<?= $form->field($model, 'id_user')->hiddenInput(['value'=> Yii::$app->user->identity->id])->label(false);?>


<?= Html::submitButton($button_text, ['class' => $class]) ?>


<?php ActiveForm::end(); ?>
</div>