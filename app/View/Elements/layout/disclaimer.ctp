<div class="bp_four_column" style='width: 951px; margin: 0 auto; position: relative;'>
  <div class="left">
    <div class="inside">
      <h3><?php echo __('Ressources'); ?></h3>
      <li>
        <a href="<?PHP echo $this->request->webroot ?>users/contact/1"><?php echo __('Contact') ?></a>
      </li>
    </div>
  </div>
  <div class="center_l">
    <div class="inside">
      <h3> … </h3>
    </div>
  </div>
  <div class="center_r">
    <div class="inside">
      <h3><?php echo __('Status'); ?></h3>
      <?php
        echo
        __('%s Entries in %s Threads; %s registred users, %s logged in, %s anonymous.',
            number_format($HeaderCounter['entries'], null, null, '.'),
            number_format($HeaderCounter['threads'], null, null, '.'),
            number_format($HeaderCounter['user'], null, null, '.'),
            $HeaderCounter['user_registered'],
            $HeaderCounter['user_anonymous']
          );
      ?>
    </div>
  </div>
  <div class="right">
    <div class="inside">
      <h3><?php echo __('About'); ?></h3>
      <p>
        <a href="http://saito.siezi.com/"><?php echo __('Powered by Saito  v%s.', Configure::read("Saito.v")); ?></a>
      </p>
    </div>
  </div>
</div>