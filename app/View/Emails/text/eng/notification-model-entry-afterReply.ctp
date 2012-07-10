Hello <?php echo $recipient['username'] . "\n" ; ?>

<?php echo $newEntry['Entry']['name']; ?> has written a new reply:

---

<?php echo $newEntry['Entry']['subject'] . "\n" ; ?>

<?php echo $newEntry['Entry']['text'] . "\n" ; ?>

---

Visit this entry: <?php echo $webroot.'entries/view/' . $newEntry['Entry']['id'] . "\n" ; ?>
<?php echo $this->element('email/text/footer'); ?>