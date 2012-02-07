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

	public function getCategoriesForAccession($accession, $fields = null, $order = null) {
		$categories = $this->find('list', array(
				'conditions' => array (
						'accession <=' => $accession,
				),
				'fields' => $fields,
				'order' => $order,
			)
		);
		return $categories;
	}

	public function getCategoriesSelectForAccession($accession) {
		$fields = array( 'Category.id', 'Category.category');
		$order = 'category_order asc';
		$categories = $this->getCategoriesForAccession($accession, $fields, $order);
		return $categories;
	}
}
?>