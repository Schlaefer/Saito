<?php
use Cake\Collection\Collection;

$this->Breadcrumbs->add(__('Smilies'), false);
?>
<?= $this->Admin->help(4) ?>
<div class="smilies index">
    <h1><?php echo __('Smilies'); ?></h1>
    <?php
    echo $this->Html->link(
        __('New Smiley'),
        ['action' => 'add'],
        ['class' => 'btn btn-primary']
    );
    ?> &nbsp; | &nbsp;
    <?php
    echo $this->Html->link(
        __('List Smiley Codes'),
        [
            'controller' => 'smiley_codes',
            'action' => 'index',
        ],
        ['class' => 'btn btn-secondary']
    );
    ?>
    <hr/>
    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th><?php echo $this->Paginator->sort('id'); ?></th>
                <th><?php echo __('Code'); ?></th>
                <th><?php echo $this->Paginator->sort('sort'); ?></th>
                <th><?php echo $this->Paginator->sort('icon'); ?></th>
                <th><?php echo $this->Paginator->sort('image'); ?></th>
                <th><?php echo $this->Paginator->sort('title'); ?></th>
                <th><?= __('Translated Title') ?></th>
                <th class="actions"><?php echo __('Actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            foreach ($smilies as $smiley) :
                $class = null;
                if ($i++ % 2 == 0) {
                    $class = ' class="altrow"';
                }

                // @bogus no better way to get the codes?
                $codes = (new Collection($smiley->get('smiley_codes')))->extract(
                    'code'
                )
                    ->toArray();
                if ($codes) {
                    $codes = implode("\n", $codes);
                } else {
                    $codes = '';
                }
                ?>
                <tr<?php echo $class;?>>
                    <td><?php echo $smiley->get('id'); ?>&nbsp;</td>
                    <td><code style="font-size: 0.85em;"><?= $codes ?></code></td>
                    <td><?php echo $smiley->get('sort'); ?>&nbsp;</td>
                    <td><?php echo $smiley->get('icon'); ?>&nbsp;</td>
                    <td><?php echo $smiley->get('image'); ?>&nbsp;</td>
                    <td><?= $smiley->get('title') ?>&nbsp;</td>
                    <td><?php echo $smiley->get('title') ? __d('nondynamic', $smiley->get('title')) : ''; ?>
                        &nbsp;</td>
                    <td class="actions">
                        <?php
                        echo $this->Html->link(
                            __('Edit'),
                            [
                                'action' => 'edit',
                                $smiley->get('id'),
                            ],
                            ['class' => 'btn btn-warning']
                        );
                        ?>
                        <?php
                        echo $this->Html->link(
                            __('Delete'),
                            [
                                'action' => 'delete',
                                $smiley->get('id'),
                            ],
                            ['class' => 'btn btn-danger'],
                            sprintf(
                                __(
                                    'Are you sure you want to delete # %s?'
                                ),
                                $smiley->get('id')
                            )
                        );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
