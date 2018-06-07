<?php
$this->start('headerSubnavRight');
echo $this->Layout->navbarItem(
    $this->Layout->textWithIcon(
        __('search_advanced'),
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
              'action' => 'simple'
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
            'placeholder' => __('search_term'),
        ]);
        $submit = $this->Form->submit(__('search_submit'), [
            'class' => 'btn btn-primary btn_search_submit'
        ]);
        $form .= $this->Html->div('form-group search_main', $text . $submit);

        $menu = '';
        $sortBy = $this->Form->radio(
            'order',
            [
              ['text' => __('Time'), 'value' => 'time'],
              ['text' => __('Rank'), 'value' => 'rank'],
            ],
            [
                'class' => 'form-check-input',
                'hiddenField' => false
            ]
        );
        $menu .= $this->Html->div('form-group form-check form-check-inline', __('Sort by: {0}', $sortBy));

        if (!empty($omittedWords)) {
            $wordLength = h(__('search.tooShort', [$omittedWords, $minWordLength]));
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
