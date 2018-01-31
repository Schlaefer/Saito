<div data-id="<?= $slidetabId; ?>"
		 class="slidetab slidetab-<?= $slidetabId ?> <?= $isOpen ? 'is-open' : '' ?>">
	<div class="slidetab-tab">
		<div class="slidetab-tab-button">
            <?php
            echo $this->fetch('slidetab-tab-button');
            $this->assign('slidetab-tab-button', '');
            ?>
        </div>
    </div>
    <div class="slidetab-outer">
        <div class="slidetab-inner">
            <?php
            echo $this->fetch('slidetab-content');
            $this->Blocks->set('slidetab-content', '');
            ?>
        </div>
    </div>
</div>
