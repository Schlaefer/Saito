<?php

	App::uses('SaitoCacheTree', 'Lib');

  class SaitoCacheTreeTest extends CakeTestCase {

    public function testIsCacheCurrent() {


      // Setup
      Cache::write('EntrySub',
          array(
            '1' => array(
                'time' => time() - 3600,
                'content' => 'foo',
                ),
          )
          );

      SaitoCacheTree::enable();
      $this->SaitoCacheTree->readCache();
      
      SaitoCacheTree::disable();
      $entry = array(
          'id'          => 1,
          'last_answer' => time() - 7200,
          );
      $result = $this->SaitoCacheTree->isCacheCurrent($entry);
      $this->assertFalse($result);

      SaitoCacheTree::enable();
      $entry = array(
          'id'          => 1,
          'last_answer' => time() - 7200,
          );
      $result = $this->SaitoCacheTree->isCacheCurrent($entry);
      $this->assertTrue($result);

      $entry = array(
          'id'          => 1,
          'last_answer' => NULL,
          );
      $result = $this->SaitoCacheTree->isCacheCurrent($entry);
      $this->assertFalse($result);

      $entry = array(
          'id'          => 1,
          );
      $result = $this->SaitoCacheTree->isCacheCurrent($entry);
      $this->assertFalse($result);

    }

		public function tearDown() {
      parent::tearDown();
      SaitoCacheTree::disable();
    }

		public function setUp() {
			$this->SaitoCacheTree = new SaitoCacheTree();
		}

  }

?>