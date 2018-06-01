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
            [
                'id' => 1,
                'smiley_id' => 1,
                'code' => ':-)',
            ],
            [
                'id' => 2,
                'smiley_id' => 1,
                'code' => ':)',
            ],
            [
                'id' => 3,
                'smiley_id' => 2,
                'code' => ':-D',
            ],
            [
                'id' => 4,
                'smiley_id' => 2,
                'code' => ':D',
            ],
            [
                'id' => 5,
                'smiley_id' => 3,
                'code' => ';-)',
            ],
            [
                'id' => 6,
                'smiley_id' => 3,
                'code' => ';)',
            ],
            [
                'id' => 7,
                'smiley_id' => 4,
                'code' => 'O:]',
            ],
            [
                'id' => 8,
                'smiley_id' => 5,
                'code' => '(-.-)zzZ',
            ],
            [
                'id' => 9,
                'smiley_id' => 6,
                'code' => 'B-)',
            ],
            [
                'id' => 10,
                'smiley_id' => 7,
                'code' => ':-*',
            ],
            [
                'id' => 11,
                'smiley_id' => 8,
                'code' => ':grinw:',
            ],
            [
                'id' => 12,
                'smiley_id' => 9,
                'code' => '[_]P',
            ],
            [
                'id' => 13,
                'smiley_id' => 9,
                'code' => ':coffee:',
            ],
            [
                'id' => 14,
                'smiley_id' => 10,
                'code' => ':P',
            ],
            [
                'id' => 15,
                'smiley_id' => 10,
                'code' => ':-P',
            ],
            [
                'id' => 16,
                'smiley_id' => 11,
                'code' => ':evil:',
            ],
            [
                'id' => 17,
                'smiley_id' => 12,
                'code' => ':blush:',
            ],
            [
                'id' => 18,
                'smiley_id' => 13,
                'code' => ':-O',
            ],
            [
                'id' => 19,
                'smiley_id' => 14,
                'code' => ':emba:',
            ],
            [
                'id' => 20,
                'smiley_id' => 14,
                'code' => ':oops:',
            ],
            [
                'id' => 21,
                'smiley_id' => 15,
                'code' => ':-(',
            ],
            [
                'id' => 22,
                'smiley_id' => 15,
                'code' => ':(',
            ],
            [
                'id' => 23,
                'smiley_id' => 16,
                'code' => ':cry:',
            ],
            [
                'id' => 24,
                'smiley_id' => 16,
                'code' => ':\'(',
            ],
            [
                'id' => 25,
                'smiley_id' => 17,
                'code' => ':angry:',
            ],
            [
                'id' => 26,
                'smiley_id' => 17,
                'code' => ':shout:',
            ]
        ];

        $table = $this->table('smiley_codes');
        $table->insert($data)->save();
    }
}
