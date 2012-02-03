<div id='search_header'>
<?php
		echo $form->create(null	, array(
				'url' => '/entries/search',
				'type' => 'get',
				'class' => 'search_form',  
				'inputDefaults' => array( 'div' => false, 'label' => false )));
		echo $form->submit(' ', array( 'div' => false, 'class' => ' btn_search_submit img_magnifier btn_search_header'));
		echo '<div>';
		echo $form->input(
						'search_term',
						array (
								'class'					=> 'search_textfield search_textfield_header',
								'placeholder'		=>__('search_submit', true),
								'value'					=> (isset($search_term)) ? $search_term : '',
						)
					);
		echo '</div>';
		echo $form->end();
?>
</div>