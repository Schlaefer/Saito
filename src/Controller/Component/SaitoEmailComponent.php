<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Log\LogTrait;
use Cake\Network\Email\DebugTransport;
use Cake\Network\Email\Email;
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

        $email = new Email('saito');
        $email->emailFormat('text')
            ->template($params['template'])
            ->from($from->toCake())
            ->to($to->toCake())
            ->subject($params['subject']);

        $params['viewVars']['message'] = $params['message'];
        $email->viewVars($params['viewVars'] + $defaults['viewVars']);

        if ($params['ccsender']) {
            $this->_sendCopyToOriginalSender($email);
        }
        $this->_send($email);
    }

    /**
     * Sends a copy of a completely configured email to the author
     *
     * @param Email $email email
     * @return void
     */
    protected function _sendCopyToOriginalSender(Email $email)
    {
        /* set new subject */
        $email = clone $email;
        $to = new SaitoEmailContact($email->to());
        $subject = $email->subject();
        $data = ['subject' => $subject, 'recipient-name' => $to->getName()];
        $subject = __('Copy of your message: ":subject" to ":recipient-name"');
        $subject = Text::insert($subject, $data);
        $email->subject($subject);

        $email->to($email->from());
        $from = new SaitoEmailContact('system');
        $email->from($from->toCake());

        $this->_send($email);
    }

    /**
     * Sends the completely configured email
     *
     * @param Email $email email
     * @return void
     */
    protected function _send(Email $email)
    {
        $debug = Configure::read('Saito.debug.email');
        if ($debug) {
            $transport = new DebugTransport();
            $email->transport($transport);
        };

        $sender = (new SaitoEmailContact('system'))->toCake();
        if ($email->from() !== $sender) {
            $email->sender($sender);
        }
        $result = $email->send();

        if ($debug) {
            $this->log($result, 'debug');
        }
    }
}
