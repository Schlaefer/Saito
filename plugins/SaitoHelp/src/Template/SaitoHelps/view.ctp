<div class="card panel-center">
    <div class="card-body richtext richtext">
        <?php
            echo $this->Html->css('SaitoHelp.saitohelp');
            echo $this->SaitoHelp->parse($help->get('text'), $CurrentUser);

        if ($isCore) {
            echo '<hr>';
            $url = "https://github.com/Schlaefer/Saito/tree/develop/docs/help/{$help->get('lang')}/{$help->get('file')}";
            echo $this->Html->link(__d('saito_help', 'improve'), $url);
        }
        ?>
    </div>
</div>
