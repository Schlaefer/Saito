<?php if (!$CurrentUser->isLoggedIn() || $this->request->params['action'] !== 'index' || $this->request->params['controller'] !== 'entries') { return; } ?>
<?php Stopwatch::start('slidetabs'); ?>
<div id="slidetabs">
	<?php
		if (!empty($slidetabs)) {
			foreach ($slidetabs as $slidetab) {
				Stopwatch::start($slidetab);
				$id = str_replace('slidetab_', '', $slidetab);

				$this->element('layout/' . $slidetab);

				$style = '';
				$style2 = '';
				if ($CurrentUser['show_' . $id] == 1) {
					$style .= 'width: 250px;';
				} else {
					$style .= 'width: 28px;';
					$style2 = 'display: none;';
				}
				?>
				<div id="slidetab_<?php echo $id; ?>" class="slidetab slidetab-<?php echo $id; ?>" style="<?php echo $style ?>" >
					<div class="slidetab-tab">
						<div class="slidetab-tab-button">
							<?php
							$remoteFunction = $this->Js->request('/users/ajax_toggle/show_' . $id);
							$this->Js->get("#slidetab_$id  .slidetab-tab-button")->event('click',
									$remoteFunction . ";layout_slidetabs_toggle('#slidetab_$id');");
							?>
							<?php
							echo $this->fetch('slidetab-header');
							$this->Blocks->set('slidetab-header', '');
							?>
						</div>
					</div> <!-- button -->
					<div  class="slidetab-content" style="<?php echo $style2 ?>" >
						<div class="content">
							<?php
							echo $this->fetch('slidetab-content');
							$this->Blocks->set('slidetab-content', '');
							?>
						</div>
					</div>
				</div>
				<?php
				Stopwatch::end($slidetab);
			}
		}
	?>
</div>
<?php Stopwatch::stop('slidetabs'); ?>