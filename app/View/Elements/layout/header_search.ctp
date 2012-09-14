<div class="top-search">
<?php
		echo $this->Form->create(null	, array(
				'url' => '/entries/search',
				'type' => 'get',
				'class' => 'search_form',  
				'inputDefaults' => array( 'div' => false, 'label' => false )));
		echo $this->Form->submit(' ', array( 'div' => false, 'class' => ' btn_search_submit img_magnifier btn_search_header'));
		echo '<div>';
		echo $this->Form->input(
						'search_term',
						array (
								'id'						=> 'header-searchField',
								'class'					=> 'search_textfield search_textfield_header',
								'placeholder'		=>__('search_submit'),
								'value'					=> (isset($search_term)) ? $search_term : '',
						)
					);
		echo '</div>';
		echo $this->Form->end();
?>
</div>