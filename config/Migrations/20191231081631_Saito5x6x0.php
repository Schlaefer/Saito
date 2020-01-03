<?php
use Migrations\AbstractMigration;

class Saito5x6x0 extends AbstractMigration
{

    public function up()
    {

        $this->execute('UPDATE drafts SET pid=0 WHERE pid IS NULL');
        $this->table('drafts')
            ->changeColumn('pid', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->update();
    }

    public function down()
    {

        $this->table('drafts')
            ->changeColumn('pid', 'integer', [
                'default' => null,
                'length' => 11,
                'null' => true,
            ])
            ->update();
        $this->execute('UPDATE drafts SET pid=NULL WHERE pid=0');
    }
}

