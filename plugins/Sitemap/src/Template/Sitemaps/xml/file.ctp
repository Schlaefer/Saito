<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<?php
		$baseUrl = $this->Sitemap->baseUrl();
		foreach ($urls as $url):
			?>
			<url>
				<loc><?= $baseUrl . $url['loc'] ?></loc>
				<lastmod><?= $url['lastmod'] ?></lastmod>
				<changefreq><?= $url['changefreq'] ?></changefreq>
			</url>
		<?php endforeach; ?>
</urlset>
