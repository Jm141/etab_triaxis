<?php
/**
 * Update Header and Footer to Use Local Resources
 */

echo "Updating header.php and footer.php to use local resources...\n\n";

// Read header.php
$headerFile = 'views/layout/header.php';
$headerContent = file_get_contents($headerFile);

// Replace CDN links with local paths
$replacements = [
    // Remove Google Fonts preconnect and replace with local
    '/<link rel="preconnect" href="https:\/\/fonts\.googleapis\.com">/' => '',
    '/<link rel="preconnect" href="https:\/\/fonts\.gstatic\.com" crossorigin>/' => '',
    '/<link href="https:\/\/fonts\.googleapis\.com\/css2\?family=Inter[^"]*" rel="stylesheet">/' => '<link rel="stylesheet" href="/tabulation/public/assets/css/inter-font.css">',
    
    // Font Awesome
    '/https:\/\/cdnjs\.cloudflare\.com\/ajax\/libs\/font-awesome\/6\.4\.0\/css\/all\.min\.css/' => '/tabulation/public/assets/css/font-awesome.min.css',
    
    // AdminLTE CSS
    '/https:\/\/cdn\.jsdelivr\.net\/npm\/admin-lte@3\.2\/dist\/css\/adminlte\.min\.css/' => '/tabulation/public/assets/css/adminlte.min.css',
];

foreach ($replacements as $pattern => $replacement) {
    $headerContent = preg_replace($pattern, $replacement, $headerContent);
}

file_put_contents($headerFile, $headerContent);
echo "✓ Updated header.php\n";

// Read footer.php
$footerFile = 'views/layout/footer.php';
$footerContent = file_get_contents($footerFile);

// Replace JavaScript CDN links
$jsReplacements = [
    // jQuery
    '/https:\/\/code\.jquery\.com\/jquery-3\.6\.0\.min\.js/' => '/tabulation/public/assets/js/jquery-3.6.0.min.js',
    
    // Bootstrap
    '/https:\/\/cdn\.jsdelivr\.net\/npm\/bootstrap@4\.6\.2\/dist\/js\/bootstrap\.bundle\.min\.js/' => '/tabulation/public/assets/js/bootstrap.bundle.min.js',
    
    // AdminLTE JS
    '/https:\/\/cdn\.jsdelivr\.net\/npm\/admin-lte@3\.2\/dist\/js\/adminlte\.min\.js/' => '/tabulation/public/assets/js/adminlte.min.js',
];

foreach ($jsReplacements as $pattern => $replacement) {
    $footerContent = preg_replace($pattern, $replacement, $footerContent);
}

file_put_contents($footerFile, $footerContent);
echo "✓ Updated footer.php\n";

echo "\n✅ All files updated to use local resources!\n";
echo "The system will now work offline.\n\n";

