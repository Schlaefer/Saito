<?php
use Migrations\AbstractMigration;

class Saitox5x1x0 extends AbstractMigration
{

    public function up()
    {

        $this->table('bookmarks')
            ->changeColumn('comment', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->update();
    }

    public function down()
    {

        $this->table('bookmarks')
            ->changeColumn('comment', 'string', [
                'default' => null,
                'length' => 255,
                'null' => false,
            ])
            ->update();
    }
}

