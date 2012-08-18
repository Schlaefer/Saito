<?php

class Smiley extends AppModel {

	public $name = 'Smiley';
	public $validate = array(
			'order' => array(
					'numeric' => array(
							'rule' => array( 'numeric' ),
					//'message' => 'Your custom message here',
					//'allowEmpty' => false,
					//'required' => false,
					//'last' => false, // Stop validation after this rule
					//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
			),
	);
	public $hasMany = array( 'SmileyCode' => array( 'className' => 'SmileyCode', 'foreignKey' => 'smiley_id' ) );

	public function load() {
		$smiliesRaw = $this->find('all', array('order' => 'Smiley.order ASC'));
		$smilies = array();
		foreach ( $smiliesRaw as $key => $smileyRaw ):
			if ( empty($smileyRaw['Smiley']['image']) ):
				$smileyRaw['Smiley']['image'] = $smileyRaw['Smiley']['icon'];
			endif;
			if ( $smileyRaw['Smiley']['title'] === NULL ):
				$smileyRaw['Smiley']['title'] = '';
			endif;
			foreach ( $smileyRaw['SmileyCode'] as $smileyRawCode ):
				unset($smileyRaw['Smiley']['id']);
				$smileyRaw['Smiley']['code'] = $smileyRawCode['code'];
				$smilies[] = $smileyRaw['Smiley'];
			endforeach;
		endforeach;
		Configure::write('Saito.Smilies.smilies_all', $smilies);
	}

}
