<?php
function getTemplateId($domain) {
    $apiUrl = "https://www.buyindiahomes.in/api/template?website=" . urlencode($domain);

    // Fetch template ID using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return null; // Return null if API call fails
    }

    $data = json_decode($response, true);
    return $data['templateId'] ?? null;
}

?>
