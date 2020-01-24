<?= $this->Html->css('SaitoSearch.saitosearch') ?>

<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();

$this->start('headerSubnavRight');
echo $this->Layout->navbarItem(
    $this->Layout->textWithIcon(h(__d('saito_search', 'simple.t')), 'search'),
    ['controller' => 'searches', 'action' => 'simple'],
    ['position' => 'right', 'escape' => false]
);
$this->end();
?>

<div class="container">
    <div class="card panel-form panel-center">
        <div class="card-body">
        <?php

        echo $this->Form->create(null, ['valueSources' => 'query', 'class']);

        echo $this->Html->div(
            'form-group',
            $this->Form->control('subject', [
                'class' => 'form-control',
                'label' => __d('saito_search', 'subject'),
            ])
        );
        echo $this->Html->div(
            'form-group',
            $this->Form->control('text', [
                'class' => 'form-control',
                'label' => __d('saito_search', 'text'),
            ])
        );
        echo $this->Html->div(
            'form-group',
            $this->Form->control('name', [
                'class' => 'form-control',
                'label' => __d('saito_search', 'name'),
            ])
        );
        ?>

        <div class="form-row">
            <div class="form-group col-sm-6">
                <?= $this->Form->label(__d('saito_search', 'lbl.categories')) ?>
                <?= $this->Form->select(
                    'category_id',
                    $categories,
                    ['class' => 'form-control', 'empty' => __d('saito_search', 'allCategories'), 'required' => false]
                )?>
            </div>
            <div class="form-group col-sm-6">
                <?= $this->Form->label(__d('saito_search', 'since.l')) ?>
                <?= $this->Form->month('month', ['class' => 'form-control mb-3', 'value' => $month]) ?>
                <?= $this->Form->year(
                    'year',
                    ['class' => 'form-control mb-3', 'minYear' => $startYear, 'maxYear' => date('Y'), 'value' => $year]
                ) ?>
            </div>
        </div>

        <?php
        echo $this->Html->div(
            'form-group',
            $this->Form->button(
                __d('saito_search', 'submit.l'),
                ['class' => 'btn btn-primary', 'type' => 'submit']
            ) . $this->SaitoHelp->icon(1)
        );
        echo $this->Form->end();
        ?>
    </div>
    </div>

    <?= $this->element('SaitoSearch.search_results') ?>

</div>
