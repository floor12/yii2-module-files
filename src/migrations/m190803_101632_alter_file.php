<?php

use yii\db\Migration;

/**
 * Class m190803_101632_alter_file
 */
class m190803_101632_alter_file extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('file-class', '{{%file}}', 'class');
        $this->createIndex('file-object_id', '{{%file}}', 'object_id');
        $this->createIndex('file-field', '{{%file}}', 'field');
        $this->createIndex('file-type', '{{%file}}', 'type');
        $this->createIndex('file-hash', '{{%file}}', 'hash');
        $this->createIndex('file-filename', '{{%file}}', 'filename');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190803_101632_alter_file cannot be reverted.\n";

        return false;
    }

}
