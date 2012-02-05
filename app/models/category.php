<?php
class Category extends AppModel {
	public $name = 'Category';
	
 	public $actsAs = array('Containable');

	public $cacheQueries = true;

	var $hasMany = array (
		"Entry" => array (
			'className' => 'Entry',
			'foreignKey' => 'category',
		)
	);

	var $validate = array(
		'category_order' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'accession' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	# @td cache

	public function getCategoriesForAccession($accession) {
		$categories = $this->find('list', array(
				'conditions' => array (
						'accession <=' => $accession,
				),
			)
		);
		return $categories;
	}
}
?>