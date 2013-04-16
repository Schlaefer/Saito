<?php $this->start('slidetab-header'); ?>
<div class="btn-slidetabShoutbox">
    <i class="icon-comment-alt icon-large"></i>
</div>
<?php $this->end('slidetab-header'); ?>
<?php $this->start('slidetab-content'); ?>
<?php  if ($CurrentUser->isLoggedIn() && $this->request->params['action'] === 'index' && $this->request->params['controller'] === 'entries') : ?>
		<div id="shoutbox">
				<form>
            <textarea maxlength="255" rows="1"></textarea>
				</form>
				<div class="shouts">
					<?= $this->element('shouts/shouts', ['shouts' => $shouts]); ?>
				</div>
		</div>
<?php endif; ?>
<?php $this->end('slidetab-content'); ?>