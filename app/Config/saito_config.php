<?php
Configure::write('Saito.theme', 'macnemo');
Configure::write('Saito.Cache.Thread', false);
Configure::write('Saito.useSaltForUserPasswords', FALSE);


Configure::write('Saito.markItUp.nextCssId', 12);
Configure::write(
		'Saito.markItUp.additionalButtons',
		array(
			'Gacker' => array(
					// image in img/markitup/<button>.png
					'button'			=> 'gacker',
					// code inserted into text
					'code' 				=> ':gacker:',
					// format replacement as image
					'type'				=> 'image',
					// replacement in output
					'replacement' => 'gacker_large.png'
				),
			'Popcorn' => array(
					'button'			=> 'popcorn', //.png
					'code' 				=> ':popcorn:',
					'type'				=> 'image',
					'replacement' => 'popcorn_large.png'
				)
			)
		);
?>