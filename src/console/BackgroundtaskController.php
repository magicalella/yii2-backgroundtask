<?php

namespace app\console\controllers;
use yii\console\Controller;
use yii\db\Query;

use Yii;
use app\models\User;
use magicalella\backgroundtask\models\Backgroundtask;
use magicalella\backgroundtask\models\BackgroundtaskSearch;
use magicalella\backgroundtask\CsvFile;
use yii\web\NotFoundHttpException;
use yii\httpclient\Client;

/**
 * TaskController implements the CRUD actions for Task model.
 */
class BackgroundtaskController extends Controller
{
	private $params;
	public $basePath = '@uploads/backgroundtask';
	public $baseUrl = '@uploads/backgroundtask/';
	public $file;
	public $page = 0;
	public $totalPage;
	public $backgroundtask;
	public $pagesize = 100;
	public $selfFileName;
	public $id_agenzia;
	public $nosave;
	public $nolog;
	public $tempTable;
	public $totalProgress;

	/**
	* nosave: non salvare lo stato
	* nolog: non salvare i log di avanzamento
	*/
    public function actionChecktask($nosave=false,$nolog=false)
    {
		//! COMMENTA $nosave = true;
		$this->nosave=$nosave;
	    $this->nolog=true; //$nolog;
		echo("start \n");
        if ($this->task=Backgroundtask::find()->where(['stato'=>Backgroundtask::STATUS_NEW])->orderBy(['id' => SORT_DESC])->one()) {
	        if (!$this->nosave) {
				$this->task->progress=0;
	    		$this->task->stato=Backgroundtask::STATUS_WORKING;
				$this->task->save();
			}
			 if (!gc_enabled()) {
	            gc_enable();
	        }

			Yii::$app->params['consoleUser']=User::find()->where(['id'=>$this->task->id_user])->one();
			Yii::$app->params['permissions']=Yii::$app->authManager->getPermissionsByUser($this->task->id_user);
			
	        $this->params=json_decode($this->task->params ?? '');
			print_r($this->params);
	        $method='_'.$this->task->action;
			
			if (method_exists($this, $method))
		    {
				echo 'eseguo '.$method;
				error_reporting(E_ALL ^ E_NOTICE); 
				ini_set('memory_limit', '4096M');
				try {
		        	return $this->$method();
				} catch (\yii\base\Exception $exception) {
					echo 'ERRORE';
					print_r($exception);
					$this->finishMessage('ERRORE - view Logs');
				}
		    } else {
				echo $method.' not exist';
				$this->finishMessage('ERRORE: '.$method.' not exist');
			}
        } else {
	        echo 'no record Found';
        }
    }

	/*
	* Inizializza Progress Bar
	*/
	private function initProgess($total) {
		$this->totalProgress=$total;
		$this->task->progress=0;
		$this->task->save();
	}

	/*
	* Aggiorna Progress Bar
	*/
	private function updateProgress($progress) {
		$this->task->progress=floor($progress*100/$this->totalProgress); 
		$this->task->save();
	}

	/*
	* Fine con file
	*/
    private function finish($closefile=true,$extension='csv') {
	    if ($closefile) $this->file->close();
		if (!$this->nosave) {
			$this->task->stato=Backgroundtask::STATUS_DONE;
		}
		$this->task->output='<a href="'.Yii::$app->params['dir_exporttask'].$this->selfFileName.'.'.$extension.'" target="_blank" download >download</a>';
		$this->task->progress=100;
		$this->task->save();
		echo("\n end");
    }
	
	/*
	* fine con messaggio
	*/
	private function finishMessage($message) {
		$this->task->stato=Backgroundtask::STATUS_DONE;
		$this->task->output=$message.' - '.date('Y-m-d H:i');
		$this->task->progress=100;
		$this->task->save();
		echo("\n end");
	}

    /**
     * Reload Task Da chron
     * Cerca il tipo task e lo esegue
     */
     
     public function actionReactivate($action)
     {
         $model = Backgroundtask::find()->where(['action'=>$action,'stato'=>Backgroundtask::STATUS_DONE])->orderBy(['id'=>SORT_DESC])->one();
         if (!$model) {
			echo 'no record Found '.$action;
			return;
		 }
         $model->stato=Backgroundtask::STATUS_NEW;	
         $model->save();
         $model->exec_task();
     }
	
	private function _export_csv_articoli() {
		$this->params->qs=(array) $this->params->qs;
		if (isset($this->params->qs['ArticoliSearch']))
			$this->params->qs['ArticoliSearch'] = (array) $this->params->qs['ArticoliSearch'] ;
		$this->file=$this->newCsvFile('ExportArticoli');
		
		$searchModel = new ArticoliSearch();
		$dataProvider = $searchModel->search($this->params->qs);
	
		$this->totalPage=ceil($dataProvider->query->count()/$this->pagesize);
	
		if ($this->file->open()) {
			$articolo=new Articoli();
			echo(memory_get_usage().' ');
			$field=[
					$articolo->getAttributeLabel('id'),
					$articolo->getAttributeLabel('title'),
					$articolo->getAttributeLabel('summary'),
					//$articolo->getAttributeLabel('content'),
					'Autore',
					'Categoria',
					$articolo->getAttributeLabel('creato_il'),
					$articolo->getAttributeLabel('modificato_il'),
					$articolo->getAttributeLabel('stato'),
			];
	
			$this->file->writeRow($field);
	
			foreach ($dataProvider->query->batch($this->pagesize) as $articoli) {
				foreach($articoli as $articolo) {
	
					$field=[
						$articolo->id,
						$articolo->title,
						$articolo->summary,
						//$articolo->content,
						$articolo->autore->username,
						($articolo->categoria)?$articolo->categoria->nome:'',
						$articolo->creato_il,
						$articolo->modificato_il,
						$articolo->statoNome,
					];
	
					$this->file->writeRow($field);
					$articolo=null;
				}
				$articoli=null;
				$this->endPage();
	
			}
			$this->finish();
		} else {
			echo 'impossibile scivere il file '.$this->file->nome ;
			$this->finishMessage('impossibile scivere il file '.$this->file->nome);
		}
	
	}
	
	/*private function _export_csv_articoli() {
		$this->params->qs=(array) $this->params->qs;
		if (isset($this->params->qs['ArticoliSearch']))
			$this->params->qs['ArticoliSearch'] = (array) $this->params->qs['ArticoliSearch'] ;
		$this->file=$this->newCsvFile('ExportArticoli');
		
		$searchModel = new ArticoliSearch();
		$dataProvider = $searchModel->search($this->params->qs);
	
		$this->totalPage=ceil($dataProvider->query->count()/$this->pagesize);
	
		if ($this->file->open()) {
			$articolo=new Articoli();
			echo(memory_get_usage().' ');
			$field=[
					$articolo->getAttributeLabel('id'),
					$articolo->getAttributeLabel('title'),
					$articolo->getAttributeLabel('summary'),
					//$articolo->getAttributeLabel('content'),
					'Autore',
					'Categoria',
					$articolo->getAttributeLabel('creato_il'),
					$articolo->getAttributeLabel('modificato_il'),
					$articolo->getAttributeLabel('stato'),
			];
	
			$this->file->writeRow($field);
	
	
			foreach ($dataProvider->query->batch($this->pagesize) as $articoli) {
				foreach($articoli as $articolo) {
	
					$field=[
						$articolo->id,
						$articolo->title,
						$articolo->summary,
						//$articolo->content,
						$articolo->autore->username,
						($articolo->categoria)?$articolo->categoria->nome:'',
						$articolo->creato_il,
						$articolo->modificato_il,
						$articolo->statoNome,
					];
	
					$this->file->writeRow($field);
					$articolo=null;
				}
				$articoli=null;
				$this->endPage();
	
			}
			$this->finish();
		} else {
			echo 'impossibile scivere il file '.$this->file->nome ;
			$this->finishMessage('impossibile scivere il file '.$this->file->nome);
		}
	
	}*/
	
	/*
	* Esempio export
	*/
	/*private function _export_rivendite() {
		$this->params->qs=(array) $this->params->qs;
		if (isset($this->params->qs['RivenditaSearch']))
			$this->params->qs['RivenditaSearch'] = (array) $this->params->qs['RivenditaSearch'] ;
		$this->file=$this->newCsvFile('ExportRivendite');
		
		$searchModel = new RivenditaSearch();
		$dataProvider = $searchModel->search($this->params->qs);

		$this->totalPage=ceil($dataProvider->query->count()/$this->pagesize);

		if ($this->file->open()) {
			$rivendita=new Rivendita();
			echo(memory_get_usage().' ');
			$field=[
					$rivendita->getAttributeLabel('id'),
					$rivendita->getAttributeLabel('posizione'),
					$rivendita->getAttributeLabel('codice'),
					$rivendita->getAttributeLabel('cliente_id'),
					$rivendita->getAttributeLabel('regione'),
					$rivendita->getAttributeLabel('provincia'),
					$rivendita->getAttributeLabel('citta'),
					'Tipo Rivendita',
					$rivendita->getAttributeLabel('contratto'),
					$rivendita->getAttributeLabel('indirizzo'),
					$rivendita->getAttributeLabel('cap'),
					'Codice Rappresentante',
					'Distanza Nucleo',
					$rivendita->getAttributeLabel('telefono'),
					$rivendita->getAttributeLabel('cellulare'),
					$rivendita->getAttributeLabel('geografia'),
					$rivendita->getAttributeLabel('area'),
					$rivendita->getAttributeLabel('vol_patentini'),
					$rivendita->getAttributeLabel('volumi_pos'),
					$rivendita->getAttributeLabel('ultimamodifica'),
					$rivendita->getAttributeLabel('stato'),
					// 'SHIFT',
					// 'AVG TESTE',
					// 'AVG C+',
					// 'AVG C+ RECRUITED',
					// 'AVG C+ EXISTING',
					// 'REDEMPTION 2 C+/TESTE',
					// 'OOS',
					// 'AVG C+ %Family per stock',
			];

			$this->file->writeRow($field);


			foreach ($dataProvider->query->batch($this->pagesize) as $rivendite) {
				foreach($rivendite as $rivendita) {

					$field=[
						$rivendita->id,
						$rivendita->posizione,
						$rivendita->codice,
						$rivendita->cliente_id,
						$rivendita->regione,
						$rivendita->provincia,
						$rivendita->citta,
						($rivendita->tiporivendita)?$rivendita->tiporivendita->descrizione:'',
						$rivendita->contratto,
						$rivendita->indirizzo,
						$rivendita->cap,
						$rivendita->rep,
						$rivendita->distanza,
						$rivendita->telefono,
						$rivendita->cellulare,
						$rivendita->geografia,
						$rivendita->area,
						$rivendita->vol_patentini,
						$rivendita->volumi_pos,
						$rivendita->ultimamodifica,
						$rivendita->stato,
					];

					$this->file->writeRow($field);
					$rivendita=null;
				}
				$rivendite=null;
				$this->endPage();

			}
			$this->finish();
		}

	}
	*/

	/*
	* Esempio Funzione del model
	*/
	/*private function _generaordini() {
		if ($count=Ordini::generaordini($this)) {
			$this->finishMessage('Generati '.$count.' Ordini');
		} else {
			$this->finishMessage('Nessu ordine generato');
		}
		return true;
	}*/
	
	/*
	* Esempio import
	*/
	/*private function _import_ordini() {
		$estensioni = ['csv'];
		$inputFile = Yii::$app->params['path_importExcel'].'importOrdini.csv';
		
		if(file_exists($inputFile)){
			echo 'File trovato';
			//$file = $inputFile;
			if(Ordini::importCSV($inputFile)){
				//unlink($inputFile);
			}
		}else{
			echo 'NESSUN FILE TROVATO';
		}
		
		$this->finishMessage('Done');
	}
	*/

	/*
	* Importa file CSV di cfSoci
	*/
	private function _import_cf() {

		$estensioni = ['csv'];
		$inputFile = Yii::$app->params['path_upload'].'cfSoci.csv';

		if(file_exists($inputFile)){
			echo 'File trovato!';
			//$file = $inputFile;
			$handle = fopen($inputFile, "r");
			list($linecount,$foo) = explode(" ", exec('wc -l ' . escapeshellarg($inputFile)));
			$lines = 0;
			$array_chiavi = [];
			
			while (($fileop = fgetcsv($handle, 1000, ";")) !== false){
				if($lines==0){
					foreach($fileop as $key=>$value){
						$root_value = str_replace(" ","", strtolower(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $value)));
						$subindex = 1;
						$value = $root_value;
						while (array_key_exists($value, $array_chiavi)) {
							$value = $root_value .'-'. $subindex;
							$subindex++;
						}

						$array_chiavi[$value] = $key;
					}
				} else { // lettura record CF
					$cf_key = '';
					if (!$cf_key) $cf_key = '';
					
					if ($cf_key) {
						$cf=CfSoci::find()->where(['cf1'=>$cf_key])->one();
						if (!$cf) $cf = new CfSoci();
						
						$cf->cag= $fileop[$array_chiavi['codiceanagrafegenerale']];
						// togli spazi all'inizio e alla fine e gli zeri iniziali
						$cf->cf1 = $cf_key;
						$cf->cf2 = '';
						$cf->socio = ($fileop[$array_chiavi['socio']]=='S')?1:0;
						//if (!$cf->cf1) $cf->cf1 = $cf->cf2;
						
						if (!$cf->save()) {
							echo('Errore salvataggio cfSoci: '.print_r($cf,true));
							print_r($fileop);
							break;
						}
					}
				}
				$this->task->progress=floor($lines*100/$linecount);
	    		$this->task->save();
				$lines ++;
			} // while
			$this->finishMessage('File importato con successo');
			unlink($inputFile);

		}else{
			echo 'NESSUN FILE TROVATO';
		}

	}

	private function _check_users() {
		echo 'Controllo utenti 1 ';
		// controlla che la tabella CFSoci sia popolata
		if (CfSoci::find()->count()<1000) {
			$this->finishMessage('ERRORE: Database CF Soci vuoto');
			return false;
		}
		// Fai chiamata API con autenticazione Bearer
		echo('Chiamata API:'.Yii::$app->params['server_endpoint']);
		$client = new Client(['baseUrl' => Yii::$app->params['server_endpoint']]);
		$request = $client->createRequest()
			->setMethod('GET')
			->setUrl('get-soci')
			->setFormat(Client::FORMAT_JSON)
			->setHeaders([
				'Authorization' => 'Bearer '.Yii::$app->params['server_token'],
			])
			->setData([]);
	
		$response = $request->send();
		if (($response->isOk) && ($response->data['status']=='success')) {
			print_r($response->data);
			$this->initProgess(count($response->data['data']));
			$elaborati=0;
			
			// loop utenti piattaforma
			foreach($response->data['data'] as $utente_piattaforma) {
				$data=false;
				$utente_piattaforma = (object)$utente_piattaforma;

				// se il cf contiene CFSoci::SKIP_CHECK salta il controllo
				if (strpos($utente_piattaforma->cf,CfSoci::SKIP_CHECK)!==false) {
					echo('Utente saltato: '.' id:'.$utente_piattaforma->id.' - '.$utente_piattaforma->cf."\n   ");
					continue;
				}

				$cf='';
				$utente_db_banca = CfSoci::find()->where(['or',['cf1'=>$cf],['cf2'=>$cf]])->one();

				if ($utente_db_banca) { // utente trovato
					echo('Utente trovato: '.' id:'.$utente_piattaforma->id.' - '.$utente_piattaforma->cf. ' - '.$utente_db_banca->cag .' - '.$utente_db_banca->socio."\n   ");
					if (($utente_db_banca->socio==1) && ($utente_piattaforma->utentetipo!=CfSoci::TIPO_SOCIO)) {
						$data = ['id'=>$utente_piattaforma->id,'tipo'=>CfSoci::TIPO_SOCIO, 'cag'=>$utente_db_banca->cag];
					} elseif (($utente_db_banca->socio==0) && ($utente_piattaforma->utentetipo!=CfSoci::TIPO_CLIENTE)) {
						$data = ['id'=>$utente_piattaforma->id,'tipo'=>CfSoci::TIPO_CLIENTE, 'cag'=>$utente_db_banca->cag];
					}	

				} else { // utente non trovato
					echo('Utente non trovato: '.$cf.' id:'.$utente_piattaforma->id."\n   ");
					if ($utente_piattaforma->utentetipo!=CfSoci::TIPO_NONCLENTE) {
						$data = ['id'=>$utente_piattaforma->id,'tipo'=>CfSoci::TIPO_NONCLENTE, 'cag'=>null];
					}
				}

				if ($data) {
					//echo('Utente aggiornato x: '.$utente_piattaforma->id."\n   ");

					$request = $client->createRequest()
						->setMethod('POST')
						->setUrl('aggiorna-stato-socio')
						//->setFormat(Client::FORMAT_JSON)
						->setHeaders([
							'Authorization' => 'Bearer '.Yii::$app->params['server_token'],
						])
						->setData($data);
					$response = $request->send();
					if (($response->isOk) && ($response->data['status']=='success')) {
						echo('Utente aggiornato: '.$utente_piattaforma->id."\n   ");
					} else {
						echo('Errore aggiornamento utente: '.$utente_piattaforma->id."\n   ");
						print_r($response->data);
					}
				}
				$this->updateProgress($elaborati++);
			}
			$this->finishMessage('Utenti controllati: '.$elaborati);
		}

	}

	private function _check_rivendite() {
		echo 'Controllo rivendite';
		// controlla che la tabella CFSoci sia popolata
		if (CfSoci::find()->count()<1000) {
			$this->finishMessage('ERRORE: Database CF Soci vuoto');
			return false;
		}
		// Fai chiamata API con autenticazione Bearer
		echo('Chiamata API:'.Yii::$app->params['server_endpoint']);
		$client = new Client(['baseUrl' => Yii::$app->params['server_endpoint']]);
		$request = $client->createRequest()
			->setMethod('GET')
			->setUrl('get-rivenditori')
			->setFormat(Client::FORMAT_JSON)
			->setHeaders([
				'Authorization' => 'Bearer '.Yii::$app->params['server_token'],
			])
			->setData([]);
	
		$response = $request->send();
		if (($response->isOk) && ($response->data['status']=='success')) {
			print_r($response->data);
			$this->initProgess(count($response->data['data']));
			$elaborati=0;
			
			// loop utenti piattaforma
			foreach($response->data['data'] as $utente_piattaforma) {
				$data=false;
				$utente_piattaforma = (object)$utente_piattaforma;

				// se il cf contiene CFSoci::SKIP_CHECK salta il controllo
				if (strpos($utente_piattaforma->cf,CfSoci::SKIP_CHECK)!==false) {
					echo('Utente saltato: '.' id:'.$utente_piattaforma->id.' - '.$utente_piattaforma->cf."\n   ");
					continue;
				}

				$cf='';
				$utente_db_banca = CfSoci::find()->where(['or',['cf1'=>$cf],['cf2'=>$cf]])->one();

				if ($utente_db_banca) { // utente trovato
					echo('Utente trovato: '.' id:'.$utente_piattaforma->id.' - '.$utente_piattaforma->cf. ' - '.$utente_db_banca->cag ."\n   ");
					if ((!$utente_piattaforma->cag)) {
						$data = ['id'=>$utente_piattaforma->id,'tipo'=>CfSoci::TIPO_CLIENTE, 'cag'=>$utente_db_banca->cag];
					}	

				} else { // utente non trovato
					echo('Utente non trovato: '.$cf.' id:'.$utente_piattaforma->id."\n   ");
					if (($utente_piattaforma->cag) || ($utente_piattaforma->utentetipo==CfSoci::TIPO_NEW)) {
						$data = ['id'=>$utente_piattaforma->id,'tipo'=>CfSoci::TIPO_NONCLENTE, 'cag'=>null];
					}
				}

				if ($data) {
					echo('Rivendita aggiornata: '.$utente_piattaforma->id."\n   ");
					
					$request = $client->createRequest()
						->setMethod('POST')
						->setUrl('aggiorna-stato-rivenditore')
						//->setFormat(Client::FORMAT_JSON)
						->setHeaders([
							'Authorization' => 'Bearer '.Yii::$app->params['server_token'],
						])
						->setData($data);
					$response = $request->send();
					if (($response->isOk) && ($response->data['status']=='success')) {
						echo('Rivendita aggiornata: '.$utente_piattaforma->id."\n   ");
					} else {
						echo('Errore aggiornamento rivendita: '.$utente_piattaforma->id."\n   ");
						print_r($response->data);
					}
				}
				$this->updateProgress($elaborati++);
			}
			$this->finishMessage('Rivendite controllate: '.$elaborati);
		}

	}

    public function endPage() {
	    gc_collect_cycles(); // svuota la memoria
		echo('| Pag.'.$this->page.' Mem:'.memory_get_usage().' ');
	    // aggiorna Task progress;
	    $this->page++;
	    $this->task->progress=floor($this->page*100/$this->totalPage);
	    $this->task->save();
    }

    public function newCsvFile($nomefile,$setensione='csv')
    {
        $this->selfFileName = $nomefile.'-' . date('Ymd') . '-'.time();

        /* @var $file CsvFile */
        $file = new CsvFile();
		
       $file->name = Yii::getAlias($this->basePath) .  $this->selfFileName . '.csv';
		//$file->name = $this->exportTaskFolder . $this->selfFileName . '.'.$setensione;
		echo 'Salvo in: '.$file->name;
		// exit();
		return $file;
    }
}
