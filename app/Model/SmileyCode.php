<?php
class SmileyCode extends AppModel {
	public  $name = 'SmileyCode';
	public  $displayField = 'code';

	public  $belongsTo = array(
		'Smiley' => array(
			'className' => 'Smiley',
			'foreignKey' => 'smiley_id',
		)
	);
}