<?php
use Migrations\AbstractMigration;
use Phinx\Db\Action\ChangeColumn;

class Saitox5x3x1 extends AbstractMigration
{
    public function up()
    {
        // Only apply on installations which made it to 5.3.0. See #347.
        if ($this->table('uploads')->hasIndex(['user_id', 'title'])) {
            // Remove existing index on title from 5.3.0
            $this->table('uploads')
                ->removeIndexByName('userId_title')
                ->update();
        }

        // Populate all NULL title fields from name
        $this->execute('UPDATE `uploads` SET `uploads`.`title`=COALESCE(`uploads`.`title`, `uploads`.`name`)');

        // Change title to NOT NULL
        $this->table('uploads')
            ->changeColumn('title', 'string', [
                'default' => null,
                'length' => 191,
                'null' => false,
            ])
            ->update();

        // Unique index on title
        $this->table('uploads')
            ->addIndex(
                [
                    'user_id',
                    'title',
                ],
                ['name' => 'userId_title', 'unique' => true]
            )
            ->update();
    }

    public function down()
    {
        $this->table('uploads')
            ->removeIndexByName('userId_title')
            ->update();

        $this->table('uploads')
            ->changeColumn('title', 'string', [
                'default' => null,
                'length' => 191,
                'null' => true,
            ])
            ->update();
    }
}
