<?php
/*
* @author    Raffaella Lollini <raffaella@kattivamente.it>
* @copyright 2023 Raffaella Lollini
*/
namespace magicalella\backgroundtask;

use Yii;
use common\models\User;
/**
 * This is the model class for table "task".
 *
 * @property int $id
 * @property int $stato
 * @property string $action
 * @property string $params
 * @property int $id_user
 * @property int $progress
 * @property string $output
 * @property string $log
 *
 * @property User $user
 */
class Backgroundtask extends \yii\db\ActiveRecord
{
	const STATUS_NEW = 0;
	const STATUS_WORKING = 1;
	const STATUS_DONE = 2;
	
	const STATI=['da Elaborare','in lavorazione','completato'];
    
    public $site_realpath;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'backgroundtask';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['action', 'id_user'], 'required'],
            [['stato', 'id_user', 'progress'], 'integer'],
            [['params', 'output', 'log'], 'string'],
            [['action'], 'string', 'max' => 50],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['id_user' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'stato' => Yii::t('app', 'Stato'),
            'action' => Yii::t('app', 'Action'),
            'params' => Yii::t('app', 'Params'),
            'id_user' => Yii::t('app', 'Id User'),
            'progress' => Yii::t('app', 'Progress'),
            'output' => Yii::t('app', 'Output'),
            'log' => Yii::t('app', 'Log'),
        ];
    }
    
    /**
     * override Find
     */

    public static function find() {
        // && (!Yii::$app->user->can('sowAllTask')) studiare come creare il ruolo
		if ((is_a(Yii::$app,'yii\web\Application'))) {
			return parent::find()->andFilterWhere([
	            'backgroundtask.id_user' => Yii::$app->user->identity->id,
	        ]);
        }
        
        return parent::find();

    }

    /**
    * Crea task
    * $task nome del task da eseguire 
    * $params parametri da passare al task
    * se import : percorso_file
    * se export : qs ->ovvero query string Yii::$app->request->queryParams         
     */
    public static function addTask($task,$params){
        $model = new Backgroundtask();
        $model->action = $task;
        $model->params = json_encode($params);
        $model->id_user = Yii::$app->user->identity->id;
        $model->progress = 0;
        $model->stato = self::STATUS_NEW;	
        if($model->save()){
            sleep(3);
            $model->exec_task();
        }
    }

    public function exec_task() {
        $site_realpath =  Yii::$app->getModule('backgroundtask')->site_realpath;
        $command='nohup '.Yii::$app->params['php'].' -d memory_limit=2048M '.$site_realpath.'/_protected/yii backgroundtask-console/checktask > '.$site_realpath.'/log_task/backgroundtasklog_'.$this->id.'.txt 2>'.$site_realpath.'/log_task/backgroundtasklog_error_'.$this->id.'.txt &';
        shell_exec($command);
        /*echo $command;
        exit (0);*/
        // controlla se eseguito da console
        if (!(Yii::$app instanceof Yii\console\Application)) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Task accodato'));
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }
}
