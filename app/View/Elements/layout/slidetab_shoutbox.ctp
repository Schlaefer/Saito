<?php $this->start('slidetab-header'); ?>
<div class="btn-slidetabShoutbox">
    <i class="icon-comment-alt icon-large"></i>
</div>
<script>
    $('#slidetab_userlist .slidetab-tab-button').on('click', function(event) {
        $('#slidetabUserlist-counter').toggle();
    });
</script>
<?php $this->end('slidetab-header'); ?>
<?php $this->start('slidetab-content'); ?>
<?php  if ($CurrentUser->isLoggedIn() && $this->request->params['action'] == 'index' && $this->request->params['controller'] == 'entries') : ?>
		<div id="shoutbox">
				<form>
            <textarea maxlength="255"></textarea>
				</form>
				<div class="shouts"></div>
		</div>
<?php  endif; ?>
<?php $this->end('slidetab-content'); ?>
