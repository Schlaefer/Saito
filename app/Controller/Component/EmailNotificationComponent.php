<?php

  App::uses('CakeEvent', 'Event');
  App::uses('CakeEventListener', 'Event');
  App::uses('SaitoUser', 'Lib');

  class EmailNotificationComponent extends Component implements CakeEventListener {

    protected $_Controller;
    protected $_Notification;

    public function startup(Controller $controller) {
      parent::startup($controller);
      $this->_Notification = ClassRegistry::init(array( 'class' => 'Notification' ));
      CakeEventManager::instance()->attach($this);
      $this->_Controller = $controller;
    }

    public function implementedEvents() {
      return array(
          'Model.User.afterActivate' => 'userActivatedAdminNotice',
      );
    }

    public function userActivatedAdminNotice($event) {
      $recipients = Configure::read('Saito.Notification.userActivatedAdminNoticeToUserWithID');
      if ( !is_array($recipients) ) return;
      $new_user = $event->data['User'];
        foreach($recipients as $recipient) :
        try {
          $this->_Controller->email(array(
              'recipient' => $recipient,
              'subject' 	=> __('Successfull registration'),
              'sender' 		=> array(
                  'User' => array(
                      'user_email' 	=> Configure::read('Saito.Settings.forum_email'),
                      'username'		=> Configure::read('Saito.Settings.forum_name')),
                  ),
              'template' 	=> Configure::read('Config.language') . DS . 'notification-admin-user_activated',
              'viewVars'  => array( 'user' => $new_user, 'ip' => env('REMOTE_ADDR')),
            ));
        } catch (Exception $exc) { }
      endforeach;

    }
  }

?>