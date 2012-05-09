<?php
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

	<div id="slidetab_<?php echo $id; ?>" class="slidebar slidebar-<?php echo $id;?>" style="<?php echo  $style ?>" >
		<div class="slidebar-tab">
			<div class="slidebar-tab-button">
				<?php
					$remoteFunction = $this->Ajax->remoteFunction(array('url' => array( 'controller' => 'users', 'action' => 'ajax_toggle', "show_$id" )));
					$this->Js->get("#slidetab_$id  .slidebar-tab-button")->event('click', $remoteFunction.";layout_slidetabs_toggle('#slidetab_$id');");
				?>
				<div class="<?php echo (isset($btn_class)) ? $btn_class : ''; ?>"><?php echo (isset($btn_content)) ? $btn_content : '&nbsp;'; ?></div>
			</div>
		</div> <!-- button -->
		<div  class="slidebar-content" style="<?php echo  $style2 ?>" >
			<div class="content">