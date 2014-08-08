<div class="panel">
  <?= $this->Layout->panelHeading($title_for_page, ['pageHeading' => true]) ?>
  <div class="panel-content staticPage">
    <?php
      echo $this->Html->css('SaitoHelp.saitohelp');
      echo $this->SaitoHelp->parse($help['text'], $CurrentUser);
    ?>
    <hr>
    <?php
      // @todo i10n
      switch ($help['lang']) {
        case 'deu':
          $title = 'Diese Hilfeseite verbessern.';
          break;
        default:
          $title = 'Improve this help-page.';
      }
      $url = "https://github.com/Schlaefer/Saito/tree/develop/docs/help/{$help['lang']}/{$help['file']}";
      echo $this->Html->link($title, $url);
    ?>
  </div>
</div>
