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
            echo $this->Form->checkbox('CatMeta.All',
                array(
                'id'		 => 'cb-category-chooser-all',
                'style'  => 'visibility: hidden;',
                'value'	 => 1));
             */
          ?>
          <input type="hidden" name="data[CatMeta][All]"
                 id="cb-category-chooser-all_" value="0">
          <input type="checkbox" name="data[CatMeta][All]"
                 id="cb-category-chooser-all" style="visibility: hidden;"
                 value="1">
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
              /* For performance reasons we generate the html manually */
              /*
            echo $this->Form->checkbox('CatChooser.' . $key,
                array(
                'onclick'			 => "$('#cb-category-chooser-all').removeAttr('checked')",
                'checked'			 => isset($categoryChooserChecked[$key]),
                'value'				 => 1));
               *
               */
            ?>
            <input type="hidden" name="data[CatChooser][<?php echo $key; ?>]"
                   id="CatChooser<?php echo $key; ?>_" value="0">
            <input type="checkbox" name="data[CatChooser][<?php echo $key; ?>]"
              <?php echo (isset($categoryChooserChecked[$key])) ? 'checked="checked"' : ''; ?>
                   onclick="$('#cb-category-chooser-all').removeAttr('checked')"
                   value="1" id="CatChooser<?php echo $key; ?>">
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
