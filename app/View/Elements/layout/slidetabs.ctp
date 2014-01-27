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
					$style .= 'width: 280px;';
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
							echo $this->fetch('slidetab-tab-button');
							$this->assign('slidetab-tab-button', '');
						?>
						</div>
					</div> <!-- button -->
					<div class="slidetab-outer" style="<?php echo $style2 ?>" >
						<div class="slidetab-inner">
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