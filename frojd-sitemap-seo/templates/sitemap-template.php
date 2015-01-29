<?php
/*
 * XML Sitemap Template
 */

header('HTTP/1.0 200 OK');
header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
?>

<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php

$sitemapSettings = get_option('sitemap_settings', '');
$sitemapSettings = explode(',', $sitemapSettings);

$queryArgs = array(
    'post_type'   => $sitemapSettings,
    'post_status' => 'publish',
    'orderby'     => 'date',
    'posts_per_page' => -1
);
query_posts($queryArgs);

if (have_posts()) : while (have_posts()) : the_post();
?>
    <url>
        <loc><?php echo get_permalink(get_the_ID()); ?></loc>
        <lastmod><?php echo mysql2date('Y-m-d\TH:i:s+00:00', get_post_modified_time('Y-m-d H:i:s', true), false); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
<?php
endwhile; endif;
?>
</urlset>
