<?php
    $this->Breadcrumbs->add(__('Plugins'), '/admin/plugins');
    $this->Breadcrumbs->add('Sitemap', false);
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
