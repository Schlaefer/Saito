Hallo, willkommen bei <?php echo Configure::read('Saito.Settings.forum_name'); ?>!

Um Ihren Account frei zu schalten, klicken Sie bitte folgenden Link an: <?= $this->Html->url(array('controller'=>'users', 'action'=>'register', $user['User']['id'], $user['User']['activate_code']), true); ?>

