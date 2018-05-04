<?php
use Cake\Core\Configure;
use \Stopwatch\Lib\Stopwatch;

Stopwatch::start('layout/disclaimer.ctp');
?>
<div class="disclaimer">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-md-4 p-3">
                <h3><?= __('Ressources') ?></h3>
                <ul>
                    <li>
                        <a href="<?= $this->request->getAttribute('webroot') ?>contacts/owner">
                        <?= __('Contact') ?></a>
                    </li>
                    <li>
                        <a href="<?= $this->request->getAttribute('webroot') ?>pages/rss_feeds">
                            <?= __('RSS') ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4 p-3">
                <h3><?= __('Status') ?></h3>
                <?= $this->cell('AppStatus') ?>
            </div>
            <div class="col-md-4 p-3">
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
</div>
<?php Stopwatch::stop('layout/disclaimer.ctp'); ?>
