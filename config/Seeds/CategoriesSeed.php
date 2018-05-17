<?php
use Migrations\AbstractSeed;

/**
 * Categories seed.
 */
class CategoriesSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
            array(
                'id' => 1,
                'category_order' => 1,
                'category' => 'Ontopic',
                'description' => '',
                'accession' => 0,
                'accession_new_thread' => 1,
                'accession_new_posting' => 1,
                'thread_count' => 0,
            ),
        ];

        $table = $this->table('categories');
        $table->insert($data)->save();
    }
}
