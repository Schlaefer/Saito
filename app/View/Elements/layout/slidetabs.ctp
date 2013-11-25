<?php
	if (	 $CurrentUser->isLoggedIn() === false
			|| $this->request->params['action'] !== 'index'
			|| $this->request->params['controller'] !== 'entries')
	{
		return;
	}
	Stopwatch::start('slidetabs');
?>
<aside id="slidetabs">
	<?php
		if (empty($slidetabs) === false) {
			foreach ($slidetabs as $slidetab) {
				Stopwatch::start($slidetab);
				$id = str_replace('slidetab_', '', $slidetab);

				$style = '';
				$style2 = '';
				$isOpen = false;
				if ($CurrentUser['show_' . $id] == 1) {
					$style .= 'width: 250px;';
					$isOpen = true;
				} else {
					$style .= 'width: 28px;';
					$style2 = 'display: none;';
				}
				$this->element('layout/' . $slidetab, array('isOpen' => $isOpen));
				?>
				<div
					data-id="<?php echo $id; ?>"
					class="slidetab slidetab-<?php echo $id; ?>"
					style="<?php echo $style ?>" >
					<div class="slidetab-tab">
						<div class="slidetab-tab-button">
						<?php
							echo $this->fetch('slidetab-header');
							$this->assign('slidetab-header', '');
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
</aside>
<?php Stopwatch::stop('slidetabs'); ?>