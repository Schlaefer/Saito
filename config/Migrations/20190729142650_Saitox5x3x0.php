<?php
use Migrations\AbstractMigration;

class Saitox5x3x0 extends AbstractMigration
{

    public function up()
    {
        $this->execute('ALTER TABLE entries ENGINE=InnoDB');
    }

    public function down()
    {
        $this->execute('ALTER TABLE entries ENGINE=MyISAM');
    }
}

