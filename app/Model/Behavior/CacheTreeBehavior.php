<?php

App::import('Lib', 'SaitoCacheTree');

/**
 * @package saito_cache_tree
 */
class CacheTreeBehavior extends ModelBehavior {
	protected $CacheTree;

	public function setup(&$model, $settings) {
		$this->CacheTree = ClassRegistry::init('SaitoCacheTree');
	}

	public function isEntryCached(&$model, $entry, $timestamp) {
		return $this->CacheTree->isEntryCached($entry, $timestamp);
	}

	public function afterSave(&$model, $created) {
		if ( $created === FALSE ) {
			//* entry is updated

			if ( isset($model->data['Entry']['subject']) || isset($model->data['Entry']['fixed']) ) {
				//* don't empty cache if non tree values like view counter are altered

				if ( isset($model->data['Entry']['tid']) ) { 
					$this->CacheTree->delete($model->data['Entry']['tid']);
				} else {
					$this->CacheTree->delete($model->id);
					}

				}
			} // $created === FALSE
		} // afterSave() 

	public function afterDelete(&$model) {
		$this->CacheTree->delete($model->id);
		}
}
?>