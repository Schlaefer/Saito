<?php
class EntryFixture extends CakeTestFixture {
	var $name = 'Entry';
	var $import = array('table'=>'entries');

	public $records = array (
		//* thread 1
		array(
			'id' => 1,
			'subject'	=> 'First_Subject',
			'text'		=> 'First_Text',
			'pid'			=> 0,
			'tid'			=> 1,
			'time'		=> '2000-01-01 20:00:00',
			// accession = 0
			'category'	=> 2,
			'user_id'		=> 3,
		),
		array(
			'id' => 2,
			'subject'	=> 'Second_Subject',
			'text'		=> 'Second_Text',
			'pid'			=> 1,
			'tid'			=> 1,
			'time'		=> '2000-01-01 20:01:00',
			'category'	=> 2,
			'user_id'		=> 2,
		),
		array(
			'id' => 3,
			'subject'	=> 'Third_Subject',
			'text'		=> 'Third_Text',
			'pid'			=> 2,
			'tid'			=> 1,
			'time'		=> '2000-01-01 20:02:00',
			'category'	=> 2,
			'user_id'		=> 3,
		),
		//* thread 2
		array(
			'id' => 4,
			'subject'	=> 'Second Thread First_Subject',
			'text'		=> '',
			'pid'			=> 0,
			'tid'			=> 4,
			'time'		=> '2000-01-01 10:00:00',
			// accession = 1
			'category'	=> 4,
			'user_id'		=> 1,

		)
	);

}
?>