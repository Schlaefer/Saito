<?php
use Migrations\AbstractSeed;

/**
 * SmileyCodes seed.
 */
class SmileyCodesSeed extends AbstractSeed
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
				'smiley_id' => 1,
				'code' => ':-)',
			),
			array(
				'id' => 2,
				'smiley_id' => 1,
				'code' => ':)',
			),
			array(
				'id' => 3,
				'smiley_id' => 2,
				'code' => ':-D',
			),
			array(
				'id' => 4,
				'smiley_id' => 2,
				'code' => ':D',
			),
			array(
				'id' => 5,
				'smiley_id' => 3,
				'code' => ';-)',
			),
			array(
				'id' => 6,
				'smiley_id' => 3,
				'code' => ';)',
			),
			array(
				'id' => 7,
				'smiley_id' => 4,
				'code' => 'O:]',
			),
			array(
				'id' => 8,
				'smiley_id' => 5,
				'code' => '(-.-)zzZ',
			),
			array(
				'id' => 9,
				'smiley_id' => 6,
				'code' => 'B-)',
			),
			array(
				'id' => 10,
				'smiley_id' => 7,
				'code' => ':-*',
			),
			array(
				'id' => 11,
				'smiley_id' => 8,
				'code' => ':grinw:',
			),
			array(
				'id' => 12,
				'smiley_id' => 9,
				'code' => '[_]P',
			),
			array(
				'id' => 13,
				'smiley_id' => 9,
				'code' => ':coffee:',
			),
			array(
				'id' => 14,
				'smiley_id' => 10,
				'code' => ':P',
			),
			array(
				'id' => 15,
				'smiley_id' => 10,
				'code' => ':-P',
			),
			array(
				'id' => 16,
				'smiley_id' => 11,
				'code' => ':evil:',
			),
			array(
				'id' => 17,
				'smiley_id' => 12,
				'code' => ':blush:',
			),
			array(
				'id' => 18,
				'smiley_id' => 13,
				'code' => ':-O',
			),
			array(
				'id' => 19,
				'smiley_id' => 14,
				'code' => ':emba:',
			),
			array(
				'id' => 20,
				'smiley_id' => 14,
				'code' => ':oops:',
			),
			array(
				'id' => 21,
				'smiley_id' => 15,
				'code' => ':-(',
			),
			array(
				'id' => 22,
				'smiley_id' => 15,
				'code' => ':(',
			),
			array(
				'id' => 23,
				'smiley_id' => 16,
				'code' => ':cry:',
			),
			array(
				'id' => 24,
				'smiley_id' => 16,
				'code' => ':\'(',
			),
			array(
				'id' => 25,
				'smiley_id' => 17,
				'code' => ':angry:',
			),
			array(
				'id' => 26,
				'smiley_id' => 17,
				'code' => ':shout:',
			)
        ];

        $table = $this->table('smiley_codes');
        $table->insert($data)->save();
    }
}
