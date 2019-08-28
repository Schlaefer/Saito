<?php
use Migrations\AbstractMigration;

class Saitox5x3x0 extends AbstractMigration
{

    public function up()
    {
        $this->table('useronline')
            ->changeColumn('logged_in', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->update();

        $this->table('drafts')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('pid', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('subject', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('text', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => '0000-00-00 00:00:00',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'pid',
                    'user_id',
                ],
                ['unique' => true]
            )
            ->addIndex(['modified'])
            ->create();

        $this->execute('ALTER TABLE entries ENGINE=InnoDB');
    }

    public function down()
    {
        $this->table('useronline')
            ->changeColumn('logged_in', 'boolean', [
                'default' => 0,
                'length' => null,
                'null' => false,
            ])
            ->update();

        $this->table('drafts')->drop()->save();

        $this->execute('ALTER TABLE entries ENGINE=MyISAM');
    }
}

