<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%backgroundtask}}`.
 */
class m250722_171651_create_backgroundtask_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%backgroundtask}}', [
            'id' => $this->primaryKey(),
            'action' => $this->string(350)->notNull(),
            'id_user' => $this->integer(11)->notNull(),
            'progress' => $this->tinyInteger()->defaultValue(0),
            'params' => $this->text(),  
            'output' => $this->text(),
            'log' => $this->text(),  
            'stato' => $this->tinyInteger()->defaultValue(0), 
            'date_add' =>   $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%backgroundtask}}');
    }
}
