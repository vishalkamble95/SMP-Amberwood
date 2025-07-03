<?php
header('Content-Type: application/json');

// Get domain dynamically
$domain = $_SERVER['HTTP_HOST'];

// Include the get-template function
include_once 'getid.php';

// Fetch template ID
$templateId = getTemplateId($domain);

if (!$templateId) {
    echo json_encode(["error" => "Failed to fetch template ID"]);
    exit;
}

// Define API URL for fetching SEO details
$seoApiUrl = "https://www.buyindiahomes.in/api/seo-detail?website=" . urlencode($domain);

// Fetch SEO data using cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $seoApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$seoResponse = curl_exec($ch);
curl_close($ch);

if (!$seoResponse) {
    echo json_encode(["error" => "Failed to fetch SEO data"]);
    exit;
}

$seoData = json_decode($seoResponse, true);

if (!$seoData || !isset($seoData['data'])) {
    echo json_encode(["error" => "Invalid API response"]);
    exit;
}

// Prepare data to be saved in `seodata.json`
$mappedData = [
    "data" => [
        "title" => $seoData['data']['title'] ?? '',
        "meta_description" => $seoData['data']['meta_description'] ?? '',
        "favicon" => $seoData['data']['favicon'] ?? '',
        "keywords" => $seoData['data']['keywords'] ?? '',
        "og_title" => $seoData['data']['og_title'] ?? '',
        "og_description" => $seoData['data']['og_description'] ?? '',
        "og_image" => $seoData['data']['og_image'] ?? '',
        "og_type" => $seoData['data']['og_type'] ?? '',
        "domain" => $seoData['data']['domain'] ?? '',
        "status" => $seoData['data']['status'] ?? '',
        "script_1" => $seoData['data']['script_1'] ?? '',
        "script_2" => $seoData['data']['script_2'] ?? '',
        "h1" => $seoData['data']['h1'] ?? '',
        "h2" => $seoData['data']['h2'] ?? '',
        "gtag_id" => $seoData['data']['gtag_id'] ?? '',
        "whatsapp_gtag_id" => $seoData['data']['whatsapp_gtag_id'] ?? '',
        "phone_conversation_number" => $seoData['data']['phone_conversation_number'] ?? '',
        "phone_conversation_id" => $seoData['data']['phone_conversation_id'] ?? '',
        "about_builder" => $seoData['data']['about_builder'] ?? '',
        "property_detail" => $seoData['data']['property_detail'] ?? ''
    ]
];

// Save to `seodata.json`
$localFilePath = __DIR__ . '/seodata.json';

// Define dynamic destination paths based on `templateId`
$destinationBasePath = "/home/q2g3j98i4rdo/seo_websites_templates/bih_seo_template_$templateId";
$destinationPath1 = "$destinationBasePath/public/seodata.json";
$destinationPath2 = "$destinationBasePath/seodata.json";

// Write JSON file locally
if (file_put_contents($localFilePath, json_encode($mappedData, JSON_PRETTY_PRINT))) {
    // Copy to first destination
    if (copy($localFilePath, $destinationPath1)) {
        echo json_encode(["success" => true, "message" => "SEO data saved and copied successfully to public"]);
    } else {
        echo json_encode(["error" => "Failed to copy SEO data to public directory"]);
    }

    // Copy to second destination
    if (copy($localFilePath, $destinationPath2)) {
        echo json_encode(["success" => true, "message" => "SEO data saved and copied successfully to template root"]);
    } else {
        echo json_encode(["error" => "Failed to copy SEO data to template root"]);
    }
} else {
    echo json_encode(["error" => "Failed to save SEO data locally"]);
}
?>
