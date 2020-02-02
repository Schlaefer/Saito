<title><?= h($titleForLayout) ?></title>
<?php
echo $this->Html->charset();
echo $this->fetch('meta');
echo $this->fetch('css');

if ($CurrentUser->isLoggedIn()) :
    echo $this->User->generateCss($CurrentUser->getSettings());
endif;

echo $this->element('layout/script_tags');
?>
<meta name="viewport" content="width=device-width"/>
