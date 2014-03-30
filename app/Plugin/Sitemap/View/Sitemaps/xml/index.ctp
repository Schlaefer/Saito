<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<?php foreach ($files as $file): ?>
		<sitemap>
			<loc>
				<?= $this->Html->url('/sitemaps/file/' . $file['url'], true) . '.xml' ?>
			</loc>
		</sitemap>
	<?php endforeach; ?>
</sitemapindex>

