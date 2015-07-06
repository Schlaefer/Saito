<?php
use Cake\Core\Configure;
use Cake\Utility\String;
use \Stopwatch\Lib\Stopwatch;

Stopwatch::start('layout/disclaimer.ctp');
?>
<div class="l-disclaimer bp-threeColumn">
    <div class="left">
        <div class="disclaimer-inside">
            <h3><?= __('Ressources') ?></h3>
            <ul>
                <li>
                    <a href="<?= $this->request->webroot ?>contacts/owner"><?= __('Contact') ?></a> </li>
                <?php /*  @td 3.0 mobile ?>
                 * <li>
                 * <a href="<?= $this->request->webroot ?>mobile/"><?=
                 * __('Mobile')
                 * ?></a>
                 * </li>
                 */ ?>
                <li>
                    <a href="<?= $this->request->webroot ?>pages/rss_feeds">
                        <?= __('RSS') ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="center">
        <div class="disclaimer-inside">
            <h3><?= __('Status') ?></h3>
            <?= $this->cell('AppStatus') ?>
        </div>
    </div>
    <div class="right">
        <div class="disclaimer-inside">
            <h3><?= __('About') ?></h3>

            <p>
                <a href="<?= Configure::read('Saito.saitoHomepage') ?>">
                    <?= __('Powered by Saito v{0}.', Configure::read("Saito.v")) ?>
                </a>
                <br/>
                <?php
                $time = Stopwatch::getWallTime(
                    Configure::read('Saito.language')
                );
                echo __('Generated in {0} s.', $time);
                ?>
            </p>
        </div>
    </div>
</div>
<?php Stopwatch::stop('layout/disclaimer.ctp'); ?>
