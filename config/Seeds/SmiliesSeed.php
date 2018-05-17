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
          array(
			array(
				'id' => 1,
				'sort' => 1,
				'icon' => 'happy',
			),
			array(
				'id' => 2,
				'sort' => 2,
				'icon' => 'grin',
			),
			array(
				'id' => 3,
				'sort' => 3,
				'icon' => 'wink',
			),
			array(
				'id' => 4,
				'sort' => 4,
				'icon' => 'saint',
			),
			array(
				'id' => 5,
				'sort' => 5,
				'icon' => 'squint',
			),
			array(
				'id' => 6,
				'sort' => 6,
				'icon' => 'sunglasses',
			),
			array(
				'id' => 7,
				'sort' => 7,
				'icon' => 'heart-empty-1',
			),
			array(
				'id' => 8,
				'sort' => 8,
				'icon' => 'thumbsup',
			),
			array(
				'id' => 9,
				'sort' => 9,
				'icon' => 'coffee',
			),
			array(
				'id' => 10,
				'sort' => 10,
				'icon' => 'tongue',
			),
			array(
				'id' => 11,
				'sort' => 11,
				'icon' => 'devil',
			),
			array(
				'id' => 12,
				'sort' => 12,
				'icon' => 'sleep',
			),
			array(
				'id' => 13,
				'sort' => 13,
				'icon' => 'surprised',
			),
			array(
				'id' => 14,
				'sort' => 14,
				'icon' => 'displeased',
			),
			array(
				'id' => 15,
				'sort' => 15,
				'icon' => 'unhappy',
			),
			array(
				'id' => 16,
				'sort' => 16,
				'icon' => 'cry',
			),
			array(
				'id' => 17,
				'sort' => 17,
				'icon' => 'angry',
			)
        );

        $table = $this->table('smilies');
        $table->insert($data)->save();
    }
}
