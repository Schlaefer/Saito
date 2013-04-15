<div class="top-search">
	<?php
	echo $this->Form->create(
		null,
		[
			'url'           => '/entries/search',
			'id'            => 'EntrySearchForm',
			'type'          => 'get',
			'class'         => 'search_form',
			'inputDefaults' => ['div' => false, 'label' => false]
		]
	);
	echo $this->Form->submit(
		' ',
		[
			'div'   => false,
			'class' => ' btn_search_submit img_magnifier btn_search_header'
		]
	);
	echo '<div>';
	echo $this->Form->input(
		'search_term',
		[
			'id'          => 'header-searchField',
			'class'       => 'search_textfield search_textfield_header',
			'placeholder' => __('search_submit'),
			'value'       => (isset($search_term)) ? $search_term : '',
		]
	);
	echo '</div>';
	echo $this->Form->end();
	?>
</div>