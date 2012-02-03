<div id="upload_index" class="upload index">
	<div class="c_header_1">
		<div>
			<div>
			</div>
			<div>
					<h2>Dateiübersicht</h2> <!-- @lo -->
			</div>
			<div class="c_last_child">
				&nbsp;
			</div>
		</div>
	</div><!-- header -->
	<div class="content">
			<?php if ( $isUploadAllowed ) : ?>
			<div class="upload_box" style="display: table;">
	<?php echo $form->create('Upload',
			array( 'action' => 'add', 'type' => 'file', 'inputDefaults' => array( 'div' => false, 'label' => false ) )); ?>
				<div style="display: table-row">
					<div class="upload_box_header" style= display:table-cell;">
						<h2><?php echo __('upload_new_title'); ?></h2>
						<br/>
									(max. <?php echo $this->Number->toReadableSize(Configure::read('Saito.Settings.upload_max_img_size') * 1024); ?> jpg, jpeg, png, gif)
					</div>
				</div>
				<div style="display: table-row;">
					<div class="upload_box_footer" style="display: table-cell; ">
						<div style="position: relative;">
	<?php echo $form->button(__("upload_btn",
					true), array( 'class' => 'btn_submit', 'type' => 'button' )); ?>
							<div style="position: absolute; z-index: 2; top:0; right: 0; width: 100%; opacity: 0; cursor: pointer; overflow: hidden; " >
				<?php echo $fileUpload->input(array( 'style' => 'width: 150px;', 'onchange' => 'this.form.submit();' )); ?>
							</div>
						</div>
					</div>
				</div>
	<?php echo $form->end(); ?>
			</div>
					<?php endif; ?>
					<?php foreach ( $images as $image ) : ?>
			<div class="upload_box" style="display: table;">
				<div style="position: absolute;">
					<div class="upload_box_delete" style="position: absolute;">
						<?php
						echo $html->link(
								$html->image('close_db.png',
										array( 'alt' => __('upload_btn_delete_upload', true) )),
								array(
								'controller' => 'uploads',
								'action' => 'delete',
								$image['Upload']['id']
								),
								array(
								'escape' => false,
								'title' => __('upload_btn_delete_upload', true),
								), 'Wirklich löschen?');
						?>
					</div>
				</div>
				<div style="display: table-row;">
					<div class="upload_box_header" style= display:table-cell;">
						<?php
						echo $html->link(
								$fileUpload->image($image['Upload']['name'],
										array( 'class' => 'upload_box_image', 'imagePathOnly' => false )),
								$fileUpload->image($image['Upload']['name'],
										array( 'imagePathOnly' => true )),
								array(
								'escape' => false,
								'target' => '_blank',
								)
						);
						?>
					</div>
				</div>
				<div style="display: table-row;">
					<div class="upload_box_footer" style="display: table-cell;">
	<?php
	$js_r = "var a = greyboxGetParentFunction('greyboxInsertIntoMarkitup') ; a(' [upload]{$image['Upload']['name']}[/upload] ');";
	echo $html->link(__('upload_btn_insert_into_posting', true), '#',
			array( 'class' => 'btn_submit', 'onclick' => $js_r . 'return false;' ))
	?>

					</div>
				</div>
			</div>
<?php endforeach; ?>
<div class="clearfix"></div>
	</div>
</div>
<div class="clearfix"></div>