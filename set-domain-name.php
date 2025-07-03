<?php
// Get the domain from the request
function setDomain() {
    return $_SERVER['HTTP_HOST'];
}

// Use the function
echo "Domain: " . setDomain();
?>
