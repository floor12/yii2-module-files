<?php

use yii\db\Migration;

/**
 * Class m230602_185211_add_alt_to_file
 */
class m230602_185211_add_alt_to_file extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn('file', 'alt', $this->string(512)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('file', 'alt');
    }
}
