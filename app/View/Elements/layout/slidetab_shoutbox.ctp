<?php $this->start('slidetab-header'); ?>
<div class="btn-slidetabShoutbox">
    <i class="icon-comment-alt icon-large"></i>
</div>
<?php $this->end('slidetab-header'); ?>
<?php $this->start('slidetab-content'); ?>
		<div id="shoutbox">
				<form>
						<textarea maxlength="255" rows="1"></textarea>
				</form>
				<div class="shouts">
					<?= $this->element('shouts/shouts', ['shouts' => $shouts]); ?>
				</div>
		</div>
<?php $this->end('slidetab-content'); ?>