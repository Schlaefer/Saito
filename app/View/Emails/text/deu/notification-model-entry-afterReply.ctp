Hallo <?php echo $recipient['username'] . "\n" ; ?>

<?php echo $newEntry['Entry']['name']; ?> hat eine neue Antwort verfasst:

---

<?php echo $newEntry['Entry']['subject'] . "\n" ; ?>

<?php echo $newEntry['Entry']['text'] . "\n" ; ?>

---

Diesen Eintrag besuchen: <?php echo $webroot.'entries/view/' . $newEntry['Entry']['id'] . "\n" ; ?>

Benachrichtigung abbestellen: <?php echo $webroot.'esnotifications/unsubscribe/' . $notification['id'] . '/token:'. $notification['deactivate'] . '/' . "\n" ; ?>
<?php echo $this->element('email/text/footer'); ?>
