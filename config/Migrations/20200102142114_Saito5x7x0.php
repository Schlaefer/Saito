<?php
use Migrations\AbstractMigration;

class Saito5x7x0 extends AbstractMigration
{

    public function up()
    {
        $this->execute('DELETE FROM `settings` WHERE `name` IN (\'text_word_maxlength\')');
    }

    public function down()
    {
    }
}

