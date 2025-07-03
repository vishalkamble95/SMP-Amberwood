<?php
// Define .htaccess content
$htaccessContent = <<<HTACCESS
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>
HTACCESS;

// Write to .htaccess file
$fileCreated = file_put_contents(".htaccess", $htaccessContent);

if ($fileCreated !== false) {
    echo ".htaccess file created successfully.";
} else {
    echo "Failed to create .htaccess file.";
}
?>
