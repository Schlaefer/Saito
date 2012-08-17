<?php

  App::uses('SaitoEntryComponent', 'Controller/Component');
  App::uses('ComponentCollection', 'Controller');

  /**
   * SaitoEntryComponent Test Case
   *
   */
  class SaitoEntryComponentTestCase extends CakeTestCase {

		public function testDummy() {
			
		}

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp() {
      parent::setUp();
      $Collection = new ComponentCollection();
      $this->SaitoEntry = new SaitoEntryComponent($Collection);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() {
      unset($this->SaitoEntry);

      parent::tearDown();
    }

  }

