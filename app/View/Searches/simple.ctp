<?php
  SDV($results, []);
  SDV($q, []);

  $this->start('headerSubnavRight');
  echo $this->Layout->navbarItem(
    $this->Layout->textWithIcon(
      __('search_advanced'),
      'search'
    ),
    ['controller' => 'searches', 'action' => 'advanced'],
    ['position' => 'right', 'escape' => false]);
  $this->end();
?>
<div class="search simple">
  <div class='searchForm-wrapper'>
    <div class='searchForm'>
      <div style="padding-left: 20%; width: 80%">
        <?php
          echo $this->Form->create(null, [
            'url' => [
              'controller' => 'searches',
              'action' => 'simple'
            ],
            'type' => 'GET',
            'class' => 'search_form',
            'inputDefaults' => ['div' => false, 'label' => false]
          ]);
          echo $this->Form->submit(__('search_submit'), [
            'div' => false,
            'class' => 'btn btn-submit btn_search_submit'
          ]);
        ?>
        <div>
          <?=
            $this->Form->input('q', [
              'div' => false,
              'id' => 'search_fulltext_textfield',
              'class' => 'search_textfield',
              'placeholder' => __('search_term'),
              'value' => $q,
              'error' => [
                'minWordLength' => __('search.q.minLength', $minWordLength)
              ]
            ]);
          ?>
        </div>
        <?= $this->Form->end(); ?>
      </div>
      <div style="width: 20%; text-align: center"></div>
    </div>
    <div class="sort-menu" style="text-align: center;">
      <?php
        $sortLink = function ($title, $o) use ($order) {
          return $this->Html->link($title,
            ['?' => ['order' => $o] + $this->request->query],
            ['class' => 'sort-menu-item' . (($order === $o) ? ' asc' : '')]
          );
        };
        $sortBy =
          $sortLink(__('Time'), 'time') .
          $sortLink(__('Rank'), 'rank');
        echo __('Sort by: %s', $sortBy);
      ?>
      &nbsp;
      <?= $this->SaitoHelp->icon(1) ?>
      <?php if (!empty($omittedWords)): ?>
        <p class="infoText">
          <?=
            h(CakeText::insert(__('search.tooShort'),
              [
                'omittedWords' => $omittedWords,
                'minChars' => $minChars
              ])) ?>
        </p>
      <?php endif; ?>
    </div>
  </div>

  <?= $this->element('searches/search_results', ['results' => $results]) ?>
  <script>
    SaitoApp.callbacks.afterViewInit.push(function() {
      $('#search_fulltext_textfield').select();
    });
  </script>
</div>