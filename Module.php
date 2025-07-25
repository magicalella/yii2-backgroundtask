<?php
/*
* @author    Raffaella Lollini <raffaella@kattivamente.it>
* @copyright 2023 Raffaella Lollini
*/
namespace magicalella\backgroundtask;

use Yii;

class Module extends \yii\base\Module
{
	/**
	 * @var string The controller namespace to use
	 */
	public $controllerNamespace = 'magicalella\backgroundtask\controllers';
	/**
	 * @inheritdoc
	 */
	public $defaultRoute = 'backgroundtask/index';
	public $site_realpath;
	public $site_root;

	/**
	 * Init module
	 */
	public function init()
	{
		parent::init();
		Yii::debug("Module backgroundtask init called", __METHOD__);
	}
}