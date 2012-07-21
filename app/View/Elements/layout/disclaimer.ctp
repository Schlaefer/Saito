<div class="l-disclaimer bp-threeColumn">
  <div class="left">
    <div class="disclaimer-inside">
      <h3><?php echo __('Ressources'); ?></h3>
      <ul>
        <li>
          <a href="<?PHP echo $this->request->webroot ?>users/contact/1"><?php echo __('Contact') ?></a>
        </li>
        <li>
          <a href="<?php echo $this->request->webroot ?>pages/rss_feeds"><?php echo __('RSS') ?></a>
        </li>
      </ul>
    </div>
  </div>
  <div class="center">
    <div class="disclaimer-inside">
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
    <div class="disclaimer-inside">
      <h3><?php echo __('About'); ?></h3>
      <p>
        <a href="http://saito.siezi.com/"><?php echo __('Powered by Saito v%s.', Configure::read("Saito.v")); ?></a>
        <br/>
        <?php echo __('Generated in %s s.', Stopwatch::getWallTime()); ?>
      </p>
    </div>
  </div>
</div>