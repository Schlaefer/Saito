<?
	$style = '';
	$style2 = '';
	if ($CurrentUser['show_' . $id ] == 1) {
		$style .= 'width: 250px;';
	}
	else {
		$style .= 'width: 28px;';
		$style2 = 'display: none;';
	}
?>

	<div id="slidetab_<?php echo $id; ?>" class="slidetab" style="<?= $style ?>" >
		<div class="button_wrapper">
			<div class="button">
				<?
					$remoteFunction = $ajax->remoteFunction(array('url' => array( 'controller' => 'users', 'action' => 'ajax_toggle', "show_$id" )));
					$js->get("#slidetab_$id  .button_wrapper")->event('click', $remoteFunction.";layout_slidetabs_toggle('#slidetab_$id');");
				?>
				<div class="<?php echo (isset($btn_class)) ? $btn_class : ''; ?>"><?php echo (isset($btn_content)) ? $btn_content : '&nbsp;'; ?></div>
			</div>
		</div> <!-- button -->
		<div  class="content_wrapper" style="<?= $style2 ?>" >
			<div class="content">