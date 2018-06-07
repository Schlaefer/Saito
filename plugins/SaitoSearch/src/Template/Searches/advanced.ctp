<?= $this->Html->css('SaitoSearch.saitosearch') ?>

<div class="container">
    <div class="card panel-form panel-center">
        <div class="card-body">
        <?php

        echo $this->Form->create(null, ['valueSources' => 'query', 'class']);

        echo $this->Html->div(
            'form-group',
            $this->Form->control('subject', ['class' => 'form-control'])
        );
        echo $this->Html->div(
            'form-group',
            $this->Form->control('text', ['class' => 'form-control'])
        );
        echo $this->Html->div(
            'form-group',
            $this->Form->control('name', ['class' => 'form-control'])
        );
        ?>

        <div class="form-row">
            <div class="form-group col-sm-6">
                <?= $this->Form->label(__('Categories')) ?>
                <?= $this->Form->select(
                    'category_id',
                    $categories,
                    ['class' => 'form-control', 'empty' => __('All Categories'), 'required' => false]
                )?>
            </div>
            <div class="form-group col-sm-6">
                <?= $this->Form->label(__('search_since')) ?>
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
                __('search_submit'),
                ['class' => 'btn btn-primary', 'type' => 'submit']
            )
        );
        echo $this->Form->end();
        ?>
    </div>
    </div>

    <?= $this->element('SaitoSearch.search_results') ?>

</div>
