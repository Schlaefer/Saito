<div>
    <?php if (!$this->request->isPreview()) : ?>
        <div class="app-prerequisites-warnings">
            <noscript>
                <div class="app-prerequisites-warning">
                    <?=
                    __(
                        'This web-application depends on JavaScript. Please activate JavaScript in your browser.'
                    )
                    ?>
                </div>
            </noscript>
        </div>
    <?php endif ?>
    <?php echo $this->fetch('script'); ?>

    <div class='clearfix'></div>
    <?php
    if ($this->get('showStopwatch')) :
        echo $this->Stopwatch->html();
    endif;
    ?>
</div>
