<?php

use yii\db\Migration;

class m180627_121715_files extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%file}}',
            [
                'id' => $this->primaryKey(11),
                'class' => $this->string(255)->notNull(),
                'field' => $this->string(255)->notNull(),
                'object_id' => $this->integer(11)->notNull()->defaultValue(0),
                'title' => $this->string(255)->notNull(),
                'filename' => $this->string(255)->notNull(),
                'content_type' => $this->string(255)->notNull(),
                'type' => $this->integer(1)->notNull(),
                'video_status' => $this->integer(1)->null()->defaultValue(null),
                'ordering' => $this->integer(11)->notNull()->defaultValue(0),
                'created' => $this->integer(11)->notNull(),
                'user_id' => $this->integer(11)->null(),
                'size' => $this->integer(20)->notNull(),
                'hash' => $this->string(255)->null(),
            ], $tableOptions
        );
    }

    public function safeDown()
    {

        $this->dropTable('{{%file}}');
    }
}
