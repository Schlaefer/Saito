<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $title_for_layout ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="apple-touch-icon" href="<?php echo $this->request->webroot . 'theme' . DS . $this->theme . DS . IMAGES_URL . 'macnemo_iphone.png'; ?>"/>
    <link rel="stylesheet" href="http://code.jquery.com/mobile/latest/jquery.mobile.min.css" />
    <script src="http://code.jquery.com/jquery.min.js"></script>
		<script src="http://code.jquery.com/mobile/latest/jquery.mobile.min.js"></script>
		<?php echo $this->Html->css('stylesheets/jquery.mobile.theme.valencia'); ?>
		<?php echo $this->Html->css('stylesheets/mobile.css'); ?>

		<script type="text/javascript">
			//set up the theme switcher on the homepage
			// @link http://jquerymobile.com/demos/1.0a4.1/docs/_assets/js/jqm-docs.js
			$('div').live('pagecreate',function(event){
				if( !$(this).is('.ui-dialog')){
					$('<a href="#themeswitcher" data-'+ $.mobile.ns +'rel="dialog" data-'+ $.mobile.ns +'transition="pop">Switch theme</a>')
					.buttonMarkup({
						'icon':'gear',
						'inline': true,
						'shadow': false,
						'theme': 'd'
					})
					.insertAfter( $(this).find('.c_footer') )
					.wrap('<div class="jqm-themeswitcher">')
					.click(function(){
						$.themeswitcher();
					});
				}
				event.stopPropagation();
			});


			//quick & dirty theme switcher, written to potentially work as a bookmarklet
			// @link http://jquerymobile.com/demos/1.0a4.1/experiments/themeswitcher/jquery.mobile.themeswitcher.js
			(function($){
				$.themeswitcher = function(){
					if( $('[data-'+ $.mobile.ns +'-url=themeswitcher]').length ){ return; }
					var themesDir = 'http://jquerymobile.com/test/themes/',
						themes = ['default','valencia'],
						currentPage = $.mobile.activePage,
						menuPage = $( '<div data-'+ $.mobile.ns +'url="themeswitcher" data-'+ $.mobile.ns +'role=\'dialog\' data-'+ $.mobile.ns +'theme=\'a\'>' +
									'<div data-'+ $.mobile.ns +'role=\'header\' data-'+ $.mobile.ns +'theme=\'b\'>' +
										'<div class=\'ui-title\'>Switch Theme:</div>'+
									'</div>'+
									'<div data-'+ $.mobile.ns +'role=\'content\' data-'+ $.mobile.ns +'theme=\'c\'><ul data-'+ $.mobile.ns +'role=\'listview\' data-'+ $.mobile.ns +'inset=\'true\'></ul></div>'+
								'</div>' )
								.appendTo( $.mobile.pageContainer ),
						menu = menuPage.find('ul');

					//menu items
					$.each(themes, function( i ){
						$('<li><a href="#" data-'+ $.mobile.ns +'rel="back">' + themes[ i ].charAt(0).toUpperCase() + themes[ i ].substr(1) + '</a></li>')
							.click(function(){
								addTheme( themes[i] );
								return false;
							})
							.appendTo(menu);
					});

					//remover, adder
					function addTheme(theme){
						$('head').append( '<link rel=\'stylesheet\' href=\''+ themesDir + theme +'/\' />' );
					}

					//create page, listview
					menuPage.page();

				};
			})(jQuery);
		</script>

	</head>
	<body>

		<?php echo $content_for_layout ?>

		<div data-role="footer" class="c_footer" >
			<div data-role="navbar">
				<ul>
					<li>
						<?php
						echo $this->Html->link(
								'Index', '/mobile/entries/index/markAsRead:1',
								array(
									'escape' => false,
								)
						);
						?>
					</li>
					<li>
						<?php
						echo $this->Html->link(
								'Letzte', '/mobile/entries/recent/markAsRead:1',
								array(
									'escape' => false,
								)
						);
						?>
					</li>

				</ul>
			</div><!-- /navbar -->
		</div><!-- /footer -->
	</div><!-- /page -->

</body>
</html>
