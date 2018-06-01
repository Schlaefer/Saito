<?php
use Migrations\AbstractSeed;

/**
 * Smilies seed.
 */
class SmiliesSeed extends AbstractSeed
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
        $data =
          [
            [
                'id' => 1,
                'sort' => 1,
                'icon' => 'happy',
            ],
            [
                'id' => 2,
                'sort' => 2,
                'icon' => 'grin',
            ],
            [
                'id' => 3,
                'sort' => 3,
                'icon' => 'wink',
            ],
            [
                'id' => 4,
                'sort' => 4,
                'icon' => 'saint',
            ],
            [
                'id' => 5,
                'sort' => 5,
                'icon' => 'squint',
            ],
            [
                'id' => 6,
                'sort' => 6,
                'icon' => 'sunglasses',
            ],
            [
                'id' => 7,
                'sort' => 7,
                'icon' => 'heart-empty-1',
            ],
            [
                'id' => 8,
                'sort' => 8,
                'icon' => 'thumbsup',
            ],
            [
                'id' => 9,
                'sort' => 9,
                'icon' => 'coffee',
            ],
            [
                'id' => 10,
                'sort' => 10,
                'icon' => 'tongue',
            ],
            [
                'id' => 11,
                'sort' => 11,
                'icon' => 'devil',
            ],
            [
                'id' => 12,
                'sort' => 12,
                'icon' => 'sleep',
            ],
            [
                'id' => 13,
                'sort' => 13,
                'icon' => 'surprised',
            ],
            [
                'id' => 14,
                'sort' => 14,
                'icon' => 'displeased',
            ],
            [
                'id' => 15,
                'sort' => 15,
                'icon' => 'unhappy',
            ],
            [
                'id' => 16,
                'sort' => 16,
                'icon' => 'cry',
            ],
            [
                'id' => 17,
                'sort' => 17,
                'icon' => 'angry',
            ]
          ];

        $table = $this->table('smilies');
        $table->insert($data)->save();
    }
}
