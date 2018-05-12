<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack('/users/view/' . $CurrentUser->getId());
$this->end();

$this->element('users/menu');
?>

<div id="js-imageUploader"/>

<script>
    SaitoApp.callbacks.afterAppInit.push(function() {
        require(['modules/uploader/uploader'], function (UploaderView) {
            'use strict';

            const Uploader = new UploaderView({el: '#js-imageUploader'});
            Uploader.render();
        });
    });
</script>
