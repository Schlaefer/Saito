<?php Stopwatch::start('category-chooser.ctp'); ?>
<div id="category-chooser" style="display: none; overflow: hidden;">
  <div class="panel">
    <div class="panel-content panel-form clearfix">
      <?php
        echo $this->Form->create(null, [
          'url' => [
            'controller' => 'users',
            'action' => 'setcategory'
          ],
          'style' => 'clear: none;',
        ]);
      ?>
      <div style="float:right; width: 150px; margin-left: 2em;">
        <p>
          <?php echo __('category_chooser_context_exp'); ?>
        </p>
      </div>

      <ul class="category-chooser-ul">
        <li class="category-chooser-li">
          <?php
            /* For performance reasons we generate the html manually */
            /*
            echo $this->Html->link(__('All'), '/users/setcategory/all')
             */
          ?>
          <a
            href="<?php echo $this->webroot; ?>users/setcategory/all"><?php echo __('All Categories'); ?></a>

        </li>
        <?php foreach ($categoryChooser as $key => $title): ?>
          <li class="category-chooser-li">
            <?php
            echo $this->Form->checkbox('CatChooser.' . $key,
                array(
                'onclick'			 => "$('#cb-category-chooser-all').removeAttr('checked')",
                'checked'			 => isset($categoryChooserChecked[$key]),
                'value'				 => 1));
            ?>
            <?php
              /* For performance reasons we generate the html manually */
              /*
              echo $this->Html->link($title, '/users/setcategory/' . $key)
               *
               */
            ?>
            <a
              href="<?php echo $this->webroot; ?>users/setcategory/<?php echo $key; ?>"><?php echo $title; ?></a>

          </li>
        <?php endforeach; ?>
      </ul>
      <?php
        echo $this->Form->submit(__('Apply'), ['class' => 'btn btn-submit']);
        echo $this->Form->end();
      ?>
    </div>
  </div>
</div>
<?php Stopwatch::end('category-chooser.ctp'); ?>
