<?php

require_once 'Lib/SaitoSeleniumTestCase.php';

class MixViewTest extends SaitoSeleniumTestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function tes1MixView()
    {
        $this->login($this);

        $thread = new SaitoTestThread($this);
        $thread->newThread();

        // open mix view
        $this->open();
        $this->click("//a[@id='btn_show_mix_{$thread->getId()}']/span");
        $this->waitForPageToLoad("30000");

        // test if mix view is opened
        $this->assertTrue($this->isElementPresent("css=.entry.mix"));
        // test if back button is present
        $this->assertTrue(
            $this->isElementPresent("//div[@class='btn-strip-back']")
        );

        // try to answer to posting
        $this->click("forum_answer_" . $thread->getId());
        // waitForElementPresent ajax call
        for ($second = 0; true; $second++) {
            if ($second >= 60) {
                $this->fail("timeout");
            }
            try {
                if ($this->isElementPresent("entry_reply")) {
                    break;
                }
            } catch (Exception $e) {
            }
            sleep(1);
        }

        $this->click("//input[@value='Eintragen']");
        $this->waitForPageToLoad("30000");
        $answer[1] = $thread->getId() + 1;

        // check if we are redirected to new entry in mix view
        $this->assertEquals(
            $this->browserUrl . 'entries/mix/' . $thread->getId(
            ) . '#' . $answer[1],
            $this->getLocation()
        );

        $thread->removeThread();
        $this->logout($this);
    }
}
