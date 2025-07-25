<?php

namespace magicalella\backgroundtask\controllers;

use Yii;
use magicalella\backgroundtask\Backgroundtask;
use magicalella\backgroundtask\BackgroundtaskSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * TaskController implements the CRUD actions for Task model.
 */
class BackgroundtaskController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index','view', 'createtask', 'update', 'delete', 'ajaxprogress', 'reload'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index','view', 'createtask', 'update', 'delete', 'ajaxprogress', 'reload'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST']
                ],
            ],
        ];
    }

    /**
     * Lists all Task models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BackgroundtaskSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Task model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    
    /**
     * Reload Task
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
     
    public function actionReload($id)
    {
        $model = $this->findModel($id);
		$model->stato=Backgroundtask::STATUS_NEW;	
		$model->save();
        $model->exec_task();

        return $this->redirect(['index']);
    }

    /**
     * Creates a new backgroundtask model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreatetask()
    {
		$post=Yii::$app->request->post();
        $model = new backgroundtask();
        if ($model->load(Yii::$app->request->post())){
            $model->id_user = Yii::$app->user->identity->id;
            $model->progress = 0;
            $model->stato = Backgroundtask::STATUS_NEW;	
            $model->save();
            sleep(3);
            
            $model->exec_task();
        }    
        
        // if (strpos($post['backgroundtask'],',')) {
        //     $post['task']=explode(',', $post['task']);
        // } else {
        //     $post['task']=[$post['task']];
        // }
        // $first=true;
        // foreach ($post['task'] as $backgroundtask) {
        //     $model = new backgroundtask();
 		//     $model->action=$backgroundtask;
		//     $model->params=$post['params'];
        //     $model->id_user=Yii::$app->user->identity->id;
		//     $model->progress=0;
		//     $model->stato=Backgroundtask::STATUS_NEW;	
		//     $model->save();
		//     
        //     // echo Yii::getAlias('@app/../').'yii backgroundtask/checktask';
        //     // exit();
        //     
        //     if (!$first) sleep(3);
        //     
        //     $model->exec_task();
// 
        //     $first=false;
        // }

        return $this->redirect(['index']);
    }

    /**
     * Updates an existing Task model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Task model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    

	/* AJAX */
    public function actionAjaxprogress($id)
    {
	    $this->layout = false;
	    $model = $this->findModel($id);
	    
	    if ($model->progress<100)
        	echo $model->progress;
        else 
        	echo $model->output;
        return;
    }

    /**
     * Finds the Task model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Task the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findModel($id)
    {
        if (($model = Backgroundtask::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
