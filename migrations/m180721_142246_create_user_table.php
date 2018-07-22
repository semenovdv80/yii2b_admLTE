<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m180721_142246_create_user_table extends Migration
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

        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'email' => $this->string()->notNull()->unique(),
            'password' => $this->string()->notNull(),
            'username' => $this->string()->null(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'auth_key' => $this->string(32)->Null(),
            'access_token' => $this->string()->null(),
            'created_at' => $this->timestamp()->Null(),
            'updated_at' => $this->timestamp()->Null(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user');
    }
}
