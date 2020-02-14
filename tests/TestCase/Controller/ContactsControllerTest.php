<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\Mailer\Message;
use Saito\Test\IntegrationTestCase;

class ContactsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.User',
        'app.UserBlock',
        'app.UserOnline',
        'app.UserIgnore',
        'app.UserRead',
        'app.Setting',
    ];

    public function testContactEmailSuccessWithCc()
    {
        $this->mockSecurity();
        $data = [
            'sender_contact' => 'fo3@example.com',
            'subject' => 'subject',
            'text' => 'text',
            'cc' => '1',
        ];

        $transproter = $this->mockMailTransporter();
        $transproter->expects($this->exactly(2))->method('send');
        // cc mail
        $transproter
            ->expects($this->at(0))
            ->method('send')
            ->with(
                $this->callback(
                    function (Message $email) {
                        $this->assertEquals(
                            $email->getFrom(),
                            ['system@example.com' => 'macnemo']
                        );
                        $this->assertEquals(
                            $email->getTo(),
                            ['fo3@example.com' => 'fo3@example.com']
                        );
                        $this->assertEmpty($email->getSender());

                        return true;
                    }
                )
            );
        // main mail
        $transproter
            ->expects($this->at(1))
            ->method('send')
            ->with(
                $this->callback(
                    function (Message $email) {
                        $this->assertEquals(
                            $email->getFrom(),
                            ['fo3@example.com' => 'fo3@example.com']
                        );
                        $this->assertEquals(
                            $email->getTo(),
                            ['contact@example.com' => 'macnemo']
                        );
                        $this->assertEquals(
                            $email->getSender(),
                            ['system@example.com' => 'macnemo']
                        );

                        return true;
                    }
                )
            );
        $this->post('/contacts/owner', $data);
        $this->assertResponseCode(302);
    }

    /**
     * tests anonymous users views contact form to owner
     */
    public function testContactOwnerByAnonShowForm()
    {
        $this->get('/contacts/owner');

        //# anon users must enter his email address
        // keep matcher in sync with testContactOwnerByUserShowForm
        $tags = [
            'input#sender-contact' => [
                'attributes' => [
                    'type' => 'email',
                ],
            ],
        ];
        $this->assertResponseContainsTags($tags);
    }

    /**
     * tests registered users views contact form to owner
     */
    public function testContactOwnerByUserShowForm()
    {
        $this->_loginUser(3);
        $this->get('/contacts/owner');

        // keep matcher in sync with testContactOwnerByAnonShowForm
        $this->assertResponseNotContains('sender-contact');
    }

    /**
     * tests anonymous sends contact form to owner with invalid email-address
     */
    public function testContactOwnerByAnonSendInvalidEmail()
    {
        $this->mockSecurity();
        $data = [
            'sender_contact' => 'foo',
            'subject' => 'Subject',
            'text' => 'text',
        ];
        $transproter = $this->mockMailTransporter();
        $transproter->expects($this->never())->method('send');

        $this->post('/contacts/owner', $data);

        $expected = 'No valid email address.';
        $this->assertResponseContains($expected);
    }

    /**
     * tests anonymous user successfully sends contact form to owner
     */
    public function testContactOwnerByAnonSendSuccess()
    {
        $this->mockSecurity();
        $transproter = $this->mockMailTransporter();

        $transproter->expects($this->once())->method('send');
        $transproter
            ->expects($this->at(0))
            ->method('send')
            ->with(
                $this->callback(
                    function (Message $email) {
                        $this->assertEquals(
                            $email->getFrom(),
                            ['fo3@example.com' => 'fo3@example.com']
                        );
                        $this->assertEquals(
                            $email->getTo(),
                            ['contact@example.com' => 'macnemo']
                        );
                        $this->assertEquals(
                            $email->getSender(),
                            ['system@example.com' => 'macnemo']
                        );
                        $this->assertStringContainsString(
                            'message-text',
                            $email->getBodyText('text')
                        );
                        $this->assertEquals($email->getSubject(), 'subject');

                        return true;
                    }
                )
            );

        $data = [
            'sender_contact' => 'fo3@example.com',
            'subject' => 'subject',
            'text' => 'message-text',
        ];
        $this->post('/contacts/owner', $data);

        $this->assertRedirect('/');
    }

    /**
     * tests registered user sends contact form to owner
     */
    public function testContactOwnerByUserSend()
    {
        $this->mockSecurity();
        $this->_loginUser(3);

        $transproter = $this->mockMailTransporter();
        $transproter->expects($this->once())->method('send');
        $transproter
            ->expects($this->at(0))
            ->method('send')
            ->with(
                $this->callback(
                    function (Message $email) {
                        $this->assertEquals(
                            $email->getFrom(),
                            ['ulysses@example.com' => 'Ulysses']
                        );
                        $this->assertEquals(
                            $email->getTo(),
                            ['contact@example.com' => 'macnemo']
                        );
                        $this->assertEquals(
                            $email->getSender(),
                            ['system@example.com' => 'macnemo']
                        );

                        return true;
                    }
                )
            );

        $data = [
            // should be ignored
            'sender_contact' => 'fo3@example.com',
            'subject' => 'subject',
            'text' => 'text',
        ];
        $this->post('/contacts/owner', $data);

        $this->assertRedirect('/');
    }

    public function testContactUserByAnon()
    {
        $url = '/contacts/user/3';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testContactUserByUserNoId()
    {
        $this->_loginUser(3);
        $this->expectException(
            '\Cake\Http\Exception\BadRequestException'
        );
        $this->get('/contacts/user/');
    }

    /**
     * Test that subject must be provided for sending an email.
     *
     * @return void
     */
    public function testContactNoSubject()
    {
        $url = '/contacts/user/3';
        $this->mockSecurity();
        $transporter = $this->mockMailTransporter();
        $transporter->expects($this->never())->method('send');
        $data = [
            'sender_contact' => 'fo3@example.com',
            'subject' => '',
            'text' => 'text',
        ];
        $this->post('/contacts/owner/', $data);
        $this->assertResponseContains('Error: Subject is empty.');
    }

    /**
     * Tests contacting user with contacting disabled fails.
     *
     * @return void
     */
    public function testContactUserContactDisabled()
    {
        $this->_loginUser(2);
        $this->expectException(
            '\Cake\Http\Exception\BadRequestException',
            1562415010
        );
        $this->get('/contacts/user/5');
    }

    /**
     * Admin is allowed to contact a user ignoring the user's personal setting
     */
    public function testContactUserContactDisabledPrivileged()
    {
        $this->_loginUser(1);

        $this->get('/contacts/user/5');

        $this->assertResponseCode(200);
        $this->assertResponseNotContains('sender-contact');
    }

    /**
     * Tests contacting a non-existing user fails.
     *
     * @return void
     */
    public function testContactUserWhoDoesNotExist()
    {
        $this->_loginUser(2);
        $this->expectException(
            '\Cake\Http\Exception\BadRequestException'
        );
        $this->get('/contacts/user/9999');
    }
}
