<?php

// Function to log messages to both error_log and browser output
function logMessage($message, $isError = false) {
    $timestamp = date("[Y-m-d H:i:s]");
    $logMessage = "$timestamp $message";
    
    // Log to error_log file
    error_log($logMessage);

    // Display message to the browser
    if ($isError) {
        echo "<span style='color:red;'>$logMessage</span><br>\n";
    } else {
        echo "$logMessage<br>\n";
    }

    // Flush output for real-time display
    ob_flush();
    flush();
}

// Get token from request parameter
$token = $_GET['token'] ?? null;

if (!$token) {
    http_response_code(401);
    die("Token is required.");
}

// Validate token via API call
function validateToken($token) {
    $url = "https://www.buyindiahomes.in/api/validatetoken?token=" . urlencode($token);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return false;
    }

    $data = json_decode($response, true);
    return isset($data['message']) && $data['message'] === "Token is valid";
}

// Check if token is valid
if (!validateToken($token)) {
    http_response_code(403);
    die("Invalid Token.");
}

logMessage("Token validation successful");

// Continue with the rest of the script...

// Start logging
ob_start();
$domain = $_SERVER['HTTP_HOST']; 
logMessage("Request received from domain: $domain");

// Validate and fetch template ID
// if (!isset($_GET['templateid']) || !is_numeric($_GET['templateid'])) {
//     logMessage("Error: Missing or invalid templateId parameter.", true);
//     exit;
// }

include_once 'getid.php';

// // Fetch template ID
$templateId = getTemplateId($domain);

if (!$templateId) {
    echo json_encode(["error" => "Failed to fetch template ID"]);
    exit;
}

$templateId = $templateId ;
// $templateId = intval($_GET['templateid']); 
logMessage("Received templateId: $templateId");

// / Execute seo-generator.php before build
$seoGeneratorUrl = "https://$domain/seo-generator.php";
logMessage("Executing SEO generator: $seoGeneratorUrl");

$seoResponse = file_get_contents($seoGeneratorUrl);
logMessage("SEO Generator Response: $seoResponse");



// Define directory structure
$baseTemplateDir = "/home/q2g3j98i4rdo/seo_websites_templates";
$projectDir = "$baseTemplateDir/bih_seo_template_$templateId";
$buildDir = "$projectDir/dist";
$targetBaseDir = "/home/q2g3j98i4rdo/websites";
$targetDir = "$targetBaseDir/$domain";

// Check if the template directory exists
if (!is_dir($projectDir)) {
    logMessage("Error: Template directory not found for template ID: $templateId", true);
    exit;
}
logMessage("Project directory found: $projectDir");

// Set environment variables
putenv("VITE_SLUG_URL=$domain");
putenv("VITE_API_URL=https://www.buyindiahomes.in/api");
putenv("RUST_BACKTRACE=1");  // Enable Rust backtrace
logMessage("Set environment variables: VITE_SLUG_URL=$domain, RUST_BACKTRACE=1");

// Change to project directory
if (!chdir($projectDir)) {
    logMessage("Error: Could not change directory to $projectDir", true);
    exit;
}
logMessage("Changed directory to $projectDir");

// Setup Node.js environment
$nvmDir = "/home/q2g3j98i4rdo/.nvm";
$nodeBin = "$nvmDir/versions/node/v22.9.0/bin";
$path = "$nodeBin:" . getenv('PATH');
putenv("PATH=$path");
putenv("RAYON_NUM_THREADS=1");

// Verify Node.js and npm existence
$nodePath = "$nodeBin/node";
$npmPath = "$nodeBin/npm";

if (!file_exists($nodePath) || !file_exists($npmPath)) {
    logMessage("Error: Node.js or npm not found at $nodeBin", true);
    exit;
}
logMessage("Using Node.js at: $nodePath");
logMessage("Using npm at: $npmPath");

// Start build process with backtrace enabled
logMessage("Starting build process...");
exec("export RUST_BACKTRACE=full && $npmPath run build 2>&1", $output, $returnVar);

// Log build output
foreach ($output as $line) {
    logMessage("Build Output: $line");
}

// Check if build was successful
if ($returnVar !== 0) {
    logMessage("Error: Build failed! Check logs for details.", true);
    exit;
}
$sitemap = "https://$domain/sitemap-generator.php";
logMessage("Executing SiteMap generator: $sitemap");

$sitemapResponse = file_get_contents($sitemap);
logMessage("SiteMap Generator Response: $sitemapResponse");


$htaccess = "https://$domain/generate-htaccess.php";
logMessage("Executing htacccess generator: $htaccess");

$htacccessResponse = file_get_contents($htaccess);
logMessage("htacccess Generator Response: $htacccessResponse");


logMessage("Build completed successfully.");

// Verify build directory
if (!is_dir($buildDir)) {
    logMessage("Error: Build directory not found: $buildDir", true);
    exit;
}

// Ensure the target directory exists
if (!is_dir($targetDir)) {
    logMessage("Creating target directory: $targetDir");
    mkdir($targetDir, 0777, true);
}

// Copy build files to target directory
logMessage("Copying build files to $targetDir...");
exec("cp -r $buildDir/* $targetDir/");

// Deployment complete
logMessage("Deployment to $domain completed successfully for Template ID: $templateId.");
?>
