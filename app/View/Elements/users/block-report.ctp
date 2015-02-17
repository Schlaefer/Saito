<?php
  if (empty($UserBlock)) {
		echo $this->element(
			'generic/no-content-yet',
			['message' => __('ncy.aub')]
		);
		return;
	}
  SDV($mode, 'profile');
  $format = ($mode === 'full') ? 'eng' : 'normal' ;
?>
<table id="blocklist" class="table table-simple <?= ($mode === 'full') ? 'table-striped' : '' ?>">
  <?php
    $headers = [
      __('user.block.active'),
      __('user.block.reason'),
      __('user.block.start'),
      __('user.block.ended'),
      __('user.block.ends'),
    ];
    if ($mode === 'full') {
      array_unshift($headers, __('user_name'));
    }
    $headers[] = __('user.block.unblock');
    echo $this->Html->tag('thead', $this->Html->tableHeaders($headers));

    $cells = [];
    foreach ($UserBlock as $block) {
      $domain = null;
      $reason = $block['reason'];
      if (strpos($reason, '.') !== false) {
        list($domain, $reason) = explode('.', $reason);
        $domain = Inflector::underscore($domain);
      }
      if ($domain) {
        $reason = __d($domain, "user.block.reason.{$reason}");
      } else {
        $by = $this->Layout->linkToUserProfile($block['By'], $CurrentUser);
        $reason = __('user.block.reason.1', $by);
      }

      $cell = [
        empty($block['ended']) ? '✓' : '–',
        $reason,
        $this->TimeH->formatTime($block['created'], $format),
        empty($block['ended']) ? '' : $this->TimeH->formatTime($block['ended'], $format),
        empty($block['ends']) ? '' : $this->Time->timeAgoInWords($block['ends'], ['accuracy' => 'hour', 'relativeStringFuture' => __d('cake', 'in %s')]),
      ];

      if ($mode === 'full') {
        array_unshift($cell, $this->Layout->linkToUserProfile($block['User'], $this->get('CurrentUser')));
      }

      $unblock = '';
      if (empty($block['ended'])) {
        $unblock = $this->Form->postLink(__('user.block.unblock'),
          ['controller' => 'users', 'action' => 'unlock', 'admin' => false,
            $block['id']]);
      }
      $cell[] = $unblock;

      $cells[] = $cell;
    }
    echo $this->Html->tag('tbody', $this->Html->tableCells($cells));
  ?>
</table>
