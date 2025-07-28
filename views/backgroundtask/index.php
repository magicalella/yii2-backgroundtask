<?php

use yii\helpers\Html;
use yii\bootstrap\Progress;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use magicalella\backgroundtask\Backgroundtask;
use yii\helpers\Url;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $searchModel magicalella\BackgroundtaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Task');
$this->params['breadcrumbs'][] = $this->title;

?>

<script>
	function updateProgress(id) {
		$.ajax({
		    url : '<?= Yii::$app->request->baseUrl. '/backgroundtask/backgroundtask/ajaxprogress' ?>',
		    type: "GET",
		    data : 'id='+id,
		    success:function(result){
			    if (result.length<=3) {
				    setTimeout(function() {
					    updateProgress(id);
					}, 3000);
					$("#progress"+id+" .progress-bar").css('width', result+'%').attr('aria-valuenow', result);
			    } else {
			    	$("#progress"+id).html(result);
			    	$("#stato"+id).html("<?= Backgroundtask::STATI[2];  ?>");
			    }
			},
		});
	}
</script>

<div class="task-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
				'hover' => true,
        'export' => false,
        'toggleData'=>false,
        'floatHeader' => true,
        'floatHeaderOptions' => ['position'=>'absolute'],
        'formatter' => [
          'class' => 'yii\\i18n\\Formatter',
          'nullDisplay' => '<span class="not-set"></span>',
        ],
        'panel' => [
          'heading' => '<h3 class="panel-title"><i class="fas fa-tasks"></i> BackgroundTask</h3>',
          'type' => 'success',
          'beforeOptions'=>['class'=>'grid_panel_remove'],
        ],
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            'id',
			[
				'attribute'=>'stato',
				'format'=>'raw',
				'value' => function ($model){
					return '<div id="stato'.$model->id.'" >'.Backgroundtask::STATI[$model->stato].'</div>';
				},
			],
//             'stato',
            'action',
//             'params:ntext',
            'user.username',

			[
				'attribute'=>'progress',
				'format'=>'raw',
				'value' => function ($model){
					if ($model->progress<100) {
						$this->registerJs('
							$(document).ready(function() {
							    setTimeout(function() {
								    updateProgress('.$model->id.');
								}, 3000);
							});
						');
						return '<div id="progress'.$model->id.'" >'.Progress::widget([
		                	'percent' => $model->progress,
			            ])."</div>";
					} else {
						return $model->output;
					}
				},
			],
//             'output:raw',
            //'log:ntext',
			[
				'label' =>'log',
				'format'=>'raw',
				'value' => function ($model){
					return '<a target="_blank" href="/log_task/backgroundtasklog_'.$model->id.'.txt" >log</a> - <a target="_blank" href="/log_task/backgroundtasklog_error_'.$model->id.'.txt" >errors</a>';
				},
			],
			[
				'attribute' => 'date_add'
			],
            ['class' => 'kartik\grid\ActionColumn',
				'template' => '{reload} {delete} {update}',
				'buttons' => [
					'reload' => function ($url,$model) {
						$url = Url::to(['reload', 'id' => $model->id]);
// 						if(Helper::checkRoute('reload')){
							return Html::a('<span class="fas fa-redo-alt"> </span>', $url,
				 				[
	                            'title' => Yii::t('app', 'Riesegui')
								]);
// 						}
					}
				]
			],
        ],
    ]); ?>
</div>
