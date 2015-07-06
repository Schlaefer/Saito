<div class="top-search">
    <?php
    echo $this->Form->create(
        null,
        [
            'url' => '/searches/simple',
            'id' => 'EntrySearchForm',
            'type' => 'get',
            'class' => 'search_form',
            'inputDefaults' => ['div' => false, 'label' => false]
        ]
    );
    echo $this->Form->button(
        "<i class='fa fa-search'></i>",
        [
            'div' => false,
            'class' => 'btn_search_submit btn_search_header',
            'escape' => false,
            'tabindex' => -1,
            'type' => 'submit'
        ]
    );
    echo '<div>';
    echo $this->Form->input(
        'q',
        [
            'id' => 'header-searchField',
            'class' => 'search_textfield search_textfield_header',
            'placeholder' => __('search_submit'),
            'value' => (isset($searchTerm)) ? $searchTerm : '',
        ]
    );
    echo '</div>';
    echo $this->Form->end();
    ?>
</div>
