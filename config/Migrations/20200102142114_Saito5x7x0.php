<?php
use Migrations\AbstractMigration;

class Saito5x7x0 extends AbstractMigration
{

    public function up()
    {
        $this->execute('DELETE FROM `settings` WHERE `name` IN (\'text_word_maxlength\')');

        $this->table('drafts')
            ->changeColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->update();
    }

    public function down()
    {
        $this->table('drafts')
            ->changeColumn('modified', 'timestamp', [
                'default' => '0000-00-00 00:00:00',
                'limit' => null,
                'null' => false,
            ])
            ->update();
    }
}
