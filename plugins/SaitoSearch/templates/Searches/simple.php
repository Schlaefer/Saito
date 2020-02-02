<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();

$this->start('headerSubnavRight');
echo $this->Layout->navbarItem(
    $this->Layout->textWithIcon(
        __d('saito_search', 'advanced.t'),
        'search'
    ),
    ['controller' => 'searches', 'action' => 'advanced'],
    ['position' => 'right', 'escape' => false]
);
$this->end();

echo $this->Html->css('SaitoSearch.saitosearch');
?>

<div class="container search simple">
    <div class="searchForm card panel-form panel-center">
        <div class="card-body">
        <?php
        $form = $this->Form->create(
            [
            'schema' => [],
            'defaults' => $searchDefaults,
            ],
            [
            'url' => [
              'controller' => 'searches',
              'action' => 'simple',
            ],
            'type' => 'GET',
            'class' => 'search_form',
            'id' => 'search_form',
            ]
        );

        $text = $this->Form->control('searchTerm', [
            // the id is bound to JS script
            'id' => 'search_fulltext_textfield',
            'class' => 'form-control search_textfield',
            'label' => false,
            'placeholder' => __d('saito_search', 'term.l'),
        ]);
        $submit = $this->Form->submit(__d('saito_search', 'submit.l'), [
            'class' => 'btn btn-primary btn_submit.l',
        ]);
        $form .= $this->Html->div('form-group search_main', $text . $submit);

        $menu = '';
        $sortBy = $this->Form->radio(
            'order',
            [
              ['text' => __d('saito_search', 'Time'), 'value' => 'time'],
              ['text' => __d('saito_search', 'Rank'), 'value' => 'rank'],
            ],
            [
                'class' => 'form-check-input',
                'hiddenField' => false,
            ]
        );
        $menu .= $this->Html->div('form-group form-check form-check-inline', __d('saito_search', 'Sort by: {0}', $sortBy));
        $menu .= $this->SaitoHelp->icon(1);

        if (!empty($omittedWords)) {
            $wordLength = h(__d('saito.search', 'tooShort', [$omittedWords, $minWordLength]));
            $wordLength .= $this->SaitoHelp->icon(1);
            $menu .= $this->Html->para('form-text small text-muted', $wordLength);
        }

        $form .= $this->Html->div('sort-menu', $menu, ['id' => '#sort_menu']);

        $form .= $this->Form->end();

        echo $form;
        ?>
    </div>
    </div>

    <?= $this->element('SaitoSearch.search_results') ?>

    <?= $this->Html->script('SaitoSearch.saito-search.min', ['block' => 'script']); ?>

</div>
