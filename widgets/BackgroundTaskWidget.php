<?php
namespace magicalella\backgroundtask\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;

class BackgroundTaskWidget extends Widget
{

	public $task; //action in controller console ex: export_csv_articoli
    public $class;
    public $title;
    public $button_text;
	public $params;//[] params
	

	/**
	 * @throws InvalidConfigException
	 */
	public function init()
    {
	    parent::init();

	    if(!$this->class) {
			$this->class = 'btn btn-success';
		}
		
		if(!$this->title) {
			$this->title = Yii::t('app','download Data');
		}
		
		if(!$this->button_text) {
			$this->button_text = Yii::t('app','download Data');
		}
		
	    
    }

    public function run($params = [])
    {
        $$renderView = 'buttonBackgroundTask';

        return $this->render($renderView, [
	        'class' => $this->class,
	        'title'  => $this->calling_controller,
            'button_text' => $this->button_text,
		    'params'  => $this->params
        ]);
    }

}
