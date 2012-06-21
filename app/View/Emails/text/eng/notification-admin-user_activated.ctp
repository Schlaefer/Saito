A new user registered successfully.

Name: <?php echo $user['username'] . "\n"; ?>
Email: <?php echo $user['user_email'] . "\n"; ?>
IP: <?php echo $ip . "\n"; ?>

Profil: <?php echo $webroot.'users/view/'.$user['id'] . "\n" ; ?>


<?php echo $this->element('email/text/footer'); ?>