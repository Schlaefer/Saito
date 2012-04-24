<?php
  echo __('register_email_content', array(
      Configure::read('Saito.Settings.forum_name'),
      $this->Html->url(array('controller'=>'users', 'action'=>'register', $user['User']['id'], $user['User']['activate_code']), true)
      )
     );
?>

<?php echo $this->element('email/text/footer'); ?>