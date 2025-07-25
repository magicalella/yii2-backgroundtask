<?php

use yii\helpers\Html;
use yii\bootstrap\Progress;

/* @var $this yii\web\View */
/* @var $model magicalella\backgroundtask\Backgroundtask */

?>
<?= Progress::widget([
    	'percent' => $progress,
    ])
?>
