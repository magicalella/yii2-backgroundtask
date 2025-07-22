Estensione per export in background dei dati in vista index grid
=======


Installazione
------------

Il modo migliore per installare questa estensione è tramite [composer](http://getcomposer.org/download/).

Lancia

```
php composer.phar require --prefer-dist magicalella/yii2-backgrountask "*"
```

o aggiungi

```
"magicalella/yii2-backgrountask": "*"
```

nella sezione require del tuo file "composer.json".

**E avvia la migrazione dei file**

yii migrate/up --migrationPath=@vendor/magicalella/yii2-backgrountask/migrations

Può essere creato manualmente. Vale a dire, la tabella `backgrountask` campi:

id(primaryKey, AUTO_INCREMENT);
action(varchar(350));
id_user(int(11));
progress(int(2));
params(text);
output(text);
log(text);
stato(int(1));

Installazione
-----

Nel file:  `backend/config/main.php` e `console/config/main.php` se yii advanced 
se Yii basic  `config/web.php` e `config/console.php`
scrivi

        'components' => [
        ...
            'backgrountask' => [
                'class'   => 'magicalella\backgrountask\backgrountask',
                'site_realpath' => '/var/www/vhosts/miosito.com',
                'site_root' => 'https://www.miosito.com'
            ],
        ...
        ]

I file esportati verranno esportati nella cartella @uploads/backgrountask

Attivazione
-----

Creazione  task
Inserire in view Index: 
----
Per creare il task utilizzare il Widget 

use magicalella\backgrountask\BackgroundTaskWidget;
<?php
    echo BackgroundTaskWidget::widget([
        'task' => 'export_csv_articoli',//action in controller console ex: export_csv_articoli
        'class' => '', //class button default is 'btn btn-success'
        'title' => '', //title button default is Yii::t('app','download Data')
        'button_text' => '', //text button default is Yii::t('app','download Data')
        'params' => [
            'qs' => Yii::$app->request->queryParams
        ] //array parmas for action task in controller console BackgrounTask exemple params for query search export model
    ]);
?>


in  _protected\console\controllers 
----
Copia il file src/console/BackgrountaskController.php

IMPORTANTE:
----
Il tuo server deve permettere il comando shell_exec


