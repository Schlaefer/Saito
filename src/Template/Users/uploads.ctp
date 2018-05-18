<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack('/users/view/' . $CurrentUser->getId());
$this->end();

$this->element('users/menu');
?>

<div id="js-imageUploader-standalone"/>
