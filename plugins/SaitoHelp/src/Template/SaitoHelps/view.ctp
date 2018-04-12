<div class="panel">
    <?= $this->Layout->panelHeading($titleForPage, ['pageHeading' => true]) ?>
    <div class="panel-content richtext">
        <?php
            echo $this->Html->css('SaitoHelp.saitohelp');
            echo $this->SaitoHelp->parse($help->get('text'), $CurrentUser);

        if ($isCore) {
            echo '<hr>';
            // @td i10n
            switch ($help->get('lang')) {
                case 'deu':
                    $title = 'Diese Hilfeseite verbessern.';
                    break;
                default:
                    $title = 'Improve this help-page.';
            }
            $url = "https://github.com/Schlaefer/Saito/tree/develop/docs/help/{$help->get('lang')}/{$help->get('file')}";
            echo $this->Html->link($title, $url);
        }
        ?>
    </div>
</div>
