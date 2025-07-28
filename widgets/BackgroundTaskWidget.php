<?php
namespace magicalella\backgroundtask\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
/*
Per creare il task utilizzare il Widget 

use magicalella\backgroundtask\widgets\BackgroundTaskWidget;
<?php
	echo BackgroundTaskWidget::widget([
		'task' => 'export_csv_articoli',//action in controller console ex: export_csv_articoli
		'button_class' => '', //class button default is 'btn btn-success'
		'title' => '', //title button default is Yii::t('app','Add Task')
		'button_text' => '', //text button default is Yii::t('app','Add Task')
		'params' => [
			'qs' => Yii::$app->request->queryParams
		] //array parmas for action task in controller console BackgroundTask exemple params for query search export model
	]);
?>
*/

class BackgroundTaskWidget extends Widget
{

	public $task; //action in controller console ex: export_csv_articoli
    public $button_class;
    public $title;
    public $button_text;
	public $params;//[] params
	

	/**
	 * @throws InvalidConfigException
	 */
	public function init()
    {
	    parent::init();

	    if(!$this->button_class) {
			$this->button_class = 'btn btn-success';
		}
		
		if(!$this->title) {
			$this->title = Yii::t('app','Add Task');
		}
		
		if(!$this->button_text) {
			$this->button_text = Yii::t('app','Add Task');
		}
		
	    
    }

    public function run($params = [])
    {
        $renderView = 'buttonBackgroundTask';

        return $this->render($renderView, [
			'task' => $this->task,
	        'class' => $this->button_class,
	        'title'  => $this->title,
            'button_text' => $this->button_text,
		    'params'  => $this->params
        ]);
    }

}
