<?php
/**
 * @var \App\View\AppView $this
 */
use Cake\Core\Configure;
use Cake\Error\Debugger;
use Saito\Exception\SaitoBlackholeException;

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error500.php');

    $this->start('file');
?>
<?php if (!empty($error->queryString)) : ?>
    <p class="notice">
        <strong>SQL Query: </strong>
        <?= h($error->queryString) ?>
    </p>
<?php endif; ?>
<?php if (!empty($error->params)) : ?>
    <strong>SQL Query Params: </strong>
    <?php Debugger::dump($error->params) ?>
<?php endif; ?>
<?php if ($error instanceof Error) : ?>
    <strong>Error in: </strong>
    <?= sprintf('%s, line %s', str_replace(ROOT, 'ROOT', $error->getFile()), $error->getLine()) ?>
<?php endif; ?>
<?php
    echo $this->element('auto_table_warning');

    $this->end();
endif;
?>
<div class="panel">
    <div class="panel-content richtext">
        <h2><?= h($message) ?></h2>
        <p>
            <strong><?= __d('cake', 'Error') ?>: </strong>
            <?= h($message) ?>
        </p>
    </div>
</div>
<?php
$shpErrorPages = [SaitoBlackholeException::class => 8];
$errorClass = get_class($error);
if (isset($shpErrorPages[$errorClass])) :
    $this->helpers()->load('SaitoHelp.SaitoHelp');
    $help = $this->SaitoHelp->icon(
        $shpErrorPages[$errorClass],
        ['label' => true]
    );
    echo $this->Html->para(null, $help);
endif;
?>
