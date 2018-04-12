<?php
    $this->Html->addCrumb(__('Plugins'), '/admin/plugins');
    $this->Html->addCrumb('Sitemap', '/admin/plugins/sitemap');
    echo $this->Html->tag('h1', 'Sitemap');

?>
<p>
    This plugin creates a sitemap containing all public postings.
</p>
<p>
    <?php
        echo $this->Html->link('Your sitemap.xml file is located here.', $this->Sitemap->sitemapUrl());
    ?>
</p>
