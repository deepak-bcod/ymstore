<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

    <!-- 1. Homepage -->
    <url>
        <loc><?php echo htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- 2. Core Discovery & Deal Pages -->
    <url>
        <loc><?php echo htmlspecialchars(base_url('daily-deals'), ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo htmlspecialchars(base_url('flash-sale'), ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo htmlspecialchars(base_url('trending-products'), ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo htmlspecialchars(base_url('newarrival-products'), ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo htmlspecialchars(base_url('shops'), ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?php echo htmlspecialchars(base_url('blogs'), ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?php echo htmlspecialchars(base_url('faqs'), ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc><?php echo htmlspecialchars(base_url('contact-us'), ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>

    <!-- 3. Product Categories -->
    <?php if (!empty($categorymenu) && is_array($categorymenu)): ?>
        <?php foreach ($categorymenu as $category): ?>
            <?php if (!empty($category->slug) && $category->slug !== 'independence-day-sale'): ?>
                <url>
                    <loc><?php echo htmlspecialchars(base_url('category/' . $category->slug), ENT_QUOTES, 'UTF-8'); ?></loc>
                    <changefreq>weekly</changefreq>
                    <priority>0.8</priority>
                </url>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 4. Products -->
    <?php if (!empty($products) && is_array($products)): ?>
        <?php foreach ($products as $product): ?>
            <?php if (!empty($product->url_key)): ?>
                <url>
                    <loc><?php echo htmlspecialchars(base_url('product-detail/' . $product->url_key), ENT_QUOTES, 'UTF-8'); ?></loc>
                    <changefreq>daily</changefreq>
                    <priority>0.7</priority>
                </url>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 5. Static CMS Pages -->
    <?php if (!empty($cms_pages) && is_array($cms_pages)): ?>
        <?php foreach ($cms_pages as $cms_page): ?>
            <?php if (!empty($cms_page->identifier) && !in_array($cms_page->identifier, array('howitstarted', '404-not-found'))): ?>
                <url>
                    <loc><?php echo htmlspecialchars(base_url('page/' . $cms_page->identifier), ENT_QUOTES, 'UTF-8'); ?></loc>
                    <changefreq>monthly</changefreq>
                    <priority>0.5</priority>
                </url>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

</urlset>
