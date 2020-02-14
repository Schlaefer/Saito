<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Log\LogTrait;
use Cake\Mailer\Mailer;
use Cake\Mailer\Message;
use Cake\Mailer\Transport\DebugTransport;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Saito\Contact\SaitoEmailContact;

class SaitoEmailComponent extends Component
{
    use LogTrait;

    /**
     * send email
     *
     * @param array $params params
     * - 'recipient' userId, predefined or User entity
     * - 'sender' userId, predefined or User entity
     * - 'ccsender' bool send carbon-copy to sender
     * - 'template' string
     * - 'message' string
     * - 'viewVars' array
     * @return void
     */
    public function email($params = [])
    {
        $defaults = [
            'ccsender' => false,
            'message' => '',
            'sender' => 'system',
            'viewVars' => [
                'forumName' => Configure::read('Saito.Settings.forum_name'),
                'webroot' => Router::url('/', true),
            ],
        ];
        $params += $defaults;

        $from = new SaitoEmailContact($params['sender']);
        $to = new SaitoEmailContact($params['recipient']);

        $mailer = new Mailer('saito');
        $mailer->viewBuilder()->setTemplate($params['template']);
        $params['viewVars']['message'] = $params['message'];
        $mailer->setViewVars($params['viewVars'] + $defaults['viewVars']);

        $email = new Message();
        $email->setEmailFormat('text')
            ->setFrom($from->toCake())
            ->setTo($to->toCake())
            ->setSubject($params['subject']);

        if ($params['ccsender']) {
            $this->_sendCopyToOriginalSender($mailer, $email);
        }

        $this->_send($mailer, $email);
    }

    /**
     * Sends a copy of a completely configured email to the author
     *
     * @param \Cake\Mailer\Mailer $mailer Mailer.
     * @param \Cake\Mailer\Message $email Email.
     * @return void
     */
    protected function _sendCopyToOriginalSender(Mailer $mailer, Message $email)
    {
        /* set new subject */
        $ccEmail = clone $email;
        $to = new SaitoEmailContact($ccEmail->getTo());
        $subject = $ccEmail->getSubject();
        $data = ['subject' => $subject, 'recipient-name' => $to->getName()];
        $subject = __('Copy of your message: ":subject" to ":recipient-name"');
        $subject = Text::insert($subject, $data);
        $ccEmail->setSubject($subject);

        $ccEmail->setTo($ccEmail->getFrom());
        $from = new SaitoEmailContact('system');
        $ccEmail->setFrom($from->toCake());

        $this->_send($mailer, $ccEmail);
    }

    /**
     * Sends the completely configured email
     *
     * @param \Cake\Mailer\Mailer $mailer Mailer.
     * @param \Cake\Mailer\Message $email Email.
     * @return void
     */
    protected function _send(Mailer $mailer, Message $email)
    {
        $sender = (new SaitoEmailContact('system'))->toCake();
        if ($email->getFrom() !== $sender) {
            $email->setSender($sender);
        }

        $debug = Configure::read('Saito.debug.email');
        if ($debug) {
            $mailer = new DebugTransport();
            $result = $mailer->send($email);

            $this->log(print_r($result, true), 'debug');

            return;
        }

        $mailer->setMessage($email)->send();
    }
}
