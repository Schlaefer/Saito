<?php
class SmileyCode extends AppModel {
	var $name = 'SmileyCode';
	var $displayField = 'code';

	var $belongsTo = array(
		'Smiley' => array(
			'className' => 'Smiley',
			'foreignKey' => 'smiley_id',
		)
	);
}
?>