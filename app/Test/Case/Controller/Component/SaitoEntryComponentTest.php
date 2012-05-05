<?php

  App::uses('SaitoEntryComponent', 'Controller/Component');
  App::uses('ComponentCollection', 'Controller');

  /**
   * SaitoEntryComponent Test Case
   *
   */
  class SaitoEntryComponentTestCase extends CakeTestCase {

    public function testIsAnsweringForbidden() {
      $result = $this->SaitoEntry->isAnsweringForbidden();
      $expected = true;
      $this->assertSame($result, $expected);
      $entry = array( 'Entry' => array( 'locked' => 0 ) );
      $result = $this->SaitoEntry->isAnsweringForbidden($entry);
      $expected = false;
      $this->assertSame($result, $expected);
      $entry = array( 'Entry' => array( 'locked' => '0' ) );
      $result = $this->SaitoEntry->isAnsweringForbidden($entry);
      $expected = false;
      $this->assertSame($result, $expected);
      $entry = array( 'Entry' => array( 'locked' => false ) );
      $result = $this->SaitoEntry->isAnsweringForbidden($entry);
      $expected = false;
      $this->assertSame($result, $expected);
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

