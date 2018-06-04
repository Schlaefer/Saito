<?= $this->Html->css('SaitoSearch.saitosearch') ?>

<div class="container">
    <div class="searchForm-wrapper">
        <?php

        echo $this->Form->create(null, ['valueSources' => 'query', 'class']);

        echo $this->Form->control('subject');
        echo $this->Form->control('text');
        echo $this->Form->control('name');

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
        echo $this->Form->button(
            __('search_submit'),
            ['class' => 'btn btn-primary', 'type' => 'submit']
        );
        echo $this->Form->end();
        ?>
    </div>

    <?= $this->element('SaitoSearch.search_results') ?>

</div>
