<?php
use Migrations\AbstractMigration;

class Saito5x4x0 extends AbstractMigration
{

    public function up()
    {
        $this->execute('DELETE FROM `settings` WHERE `name` IN (\'block_user_ui\')');
    }

    public function down()
    {
    }
}

