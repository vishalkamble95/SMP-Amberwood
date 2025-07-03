<?php

// Load environment variables
// $envDomain = getenv('VITE_SLUG_URL'); // Get domain from env variable
$envDomain = $_SERVER['HTTP_HOST'];

define('DEFAULT_DOMAIN', $envDomain ?: 'yourdefaultdomain.com'); // Fallback if not set

// API URLs (Replace with actual endpoints)
define('API_GET_BLOG', 'https://www.buyindiahomes.in/api/blogs?website=');
define('API_GET_PROPERTIES', 'https://www.buyindiahomes.in/api/get-properties?website=');

// Use the environment domain as the final domain
$finalDomain = DEFAULT_DOMAIN;

// Fetch blog data
$blogResponse = file_get_contents(API_GET_BLOG . $finalDomain);
$propertyResponse = file_get_contents(API_GET_PROPERTIES . $finalDomain);

if ($blogResponse === false || $propertyResponse === false) {
    die("Error fetching data from API");
}

// Convert JSON responses to arrays
$blogData = json_decode($blogResponse, true);
$propertyData = json_decode($propertyResponse, true);

// Ensure data is valid
if (!isset($blogData['blogs']) || !is_array($blogData['blogs'])) {
    die("Invalid blogs data format");
}

if (!isset($propertyData['property_details']) || !is_array($propertyData['property_details'])) {
    die("Invalid property data format");
}

// Start XML structure
$sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
// $latestUpdate = max(
//     array_column($blogData['blogs'], 'updated_at'),
//     array_column($propertyData['property_details'], 'updated_at')
// );
$homepageLastMod = date('Y-m-d');

// Add homepage
$sitemap .= "
    <url>
        <loc>https://$finalDomain/</loc>
        <lastmod>$homepageLastMod</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
";

// Add blogs to sitemap
foreach ($blogData['blogs'] as $blog) {
    if (!isset($blog['post_slug'])) {
        continue; // Skip if post_slug is missing
    }
    
    $lastMod = date('Y-m-d', strtotime($blog['updated_at']));

    $sitemap .= "
        <url>
            <loc>https://$finalDomain/blogs/{$blog['post_slug']}</loc>
             <lastmod>$lastMod</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    ";
}

// Add properties to sitemap
foreach ($propertyData['property_details'] as $property) {
    if (!isset($property['property_slug']) || strpos($property['property_slug'], '.com') !== false) {
        continue; // Skip if property_slug is missing or contains ".com"
    }
    $lastMod = date('Y-m-d', strtotime($property['updated_at']));
    $sitemap .= "
        <url>
            <loc>https://$finalDomain/studios/{$property['property_slug']}</loc>
             <lastmod>$lastMod</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    ";
}

// Close XML
$sitemap .= '</urlset>';

// Save to sitemap.xml file
$filePath = __DIR__ . "/sitemap.xml";
file_put_contents($filePath, $sitemap);

echo "Sitemap generated successfully: <a href='sitemap.xml'>View Sitemap</a>";

?>