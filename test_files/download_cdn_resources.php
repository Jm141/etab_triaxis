<?php
/**
 * Download CDN Resources for Local Use
 * This script downloads all CDN resources to run the system offline
 */

echo "========================================\n";
echo "Downloading CDN Resources\n";
echo "========================================\n\n";

// Create directories
$directories = [
    'public/assets/css',
    'public/assets/js',
    'public/assets/fonts',
    'public/assets/fonts/fontawesome',
    'public/assets/fonts/inter'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ Created directory: $dir\n";
    }
}

// Resources to download
$resources = [
    // CSS Files
    [
        'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        'path' => 'public/assets/css/font-awesome.min.css',
        'type' => 'css'
    ],
    [
        'url' => 'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css',
        'path' => 'public/assets/css/adminlte.min.css',
        'type' => 'css'
    ],
    [
        'url' => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
        'path' => 'public/assets/css/inter-font.css',
        'type' => 'css'
    ],
    
    // JavaScript Files
    [
        'url' => 'https://code.jquery.com/jquery-3.6.0.min.js',
        'path' => 'public/assets/js/jquery-3.6.0.min.js',
        'type' => 'js'
    ],
    [
        'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
        'path' => 'public/assets/js/bootstrap.bundle.min.js',
        'type' => 'js'
    ],
    [
        'url' => 'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js',
        'path' => 'public/assets/js/adminlte.min.js',
        'type' => 'js'
    ]
];

// Download function
function downloadFile($url, $path) {
    echo "Downloading: " . basename($path) . "... ";
    
    $ch = curl_init($url);
    $fp = fopen($path, 'wb');
    
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    
    $success = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    fclose($fp);
    
    if ($success && $httpCode == 200 && file_exists($path) && filesize($path) > 0) {
        echo "✓ Success (" . number_format(filesize($path)) . " bytes)\n";
        return true;
    } else {
        echo "✗ Failed (HTTP $httpCode)\n";
        if (file_exists($path)) {
            unlink($path);
        }
        return false;
    }
}

// Download Font Awesome fonts
function downloadFontAwesomeFonts() {
    echo "\nDownloading Font Awesome fonts...\n";
    
    $fontFiles = [
        'fa-solid-900.woff2' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2',
        'fa-regular-400.woff2' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-regular-400.woff2',
        'fa-brands-400.woff2' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-brands-400.woff2',
    ];
    
    foreach ($fontFiles as $filename => $url) {
        $path = 'public/assets/fonts/fontawesome/' . $filename;
        downloadFile($url, $path);
    }
}

// Download Inter font
function downloadInterFont() {
    echo "\nDownloading Inter font...\n";
    
    // Inter font files (from Google Fonts)
    $fontFiles = [
        'Inter-Light.woff2' => 'https://fonts.gstatic.com/s/inter/v13/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hiA.woff2',
        'Inter-Regular.woff2' => 'https://fonts.gstatic.com/s/inter/v13/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hiA.woff2',
        'Inter-Medium.woff2' => 'https://fonts.gstatic.com/s/inter/v13/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hiA.woff2',
        'Inter-SemiBold.woff2' => 'https://fonts.gstatic.com/s/inter/v13/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hiA.woff2',
        'Inter-Bold.woff2' => 'https://fonts.gstatic.com/s/inter/v13/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hiA.woff2',
    ];
    
    // Note: We'll create a local @font-face CSS instead
    echo "Creating local Inter font CSS...\n";
    
    $interCSS = '@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 300;
    font-display: swap;
    src: url("../fonts/inter/Inter-Light.woff2") format("woff2");
}
@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url("../fonts/inter/Inter-Regular.woff2") format("woff2");
}
@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 500;
    font-display: swap;
    src: url("../fonts/inter/Inter-Medium.woff2") format("woff2");
}
@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 600;
    font-display: swap;
    src: url("../fonts/inter/Inter-SemiBold.woff2") format("woff2");
}
@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    src: url("../fonts/inter/Inter-Bold.woff2") format("woff2");
}
@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 800;
    font-display: swap;
    src: url("../fonts/inter/Inter-Bold.woff2") format("woff2");
}';
    
    file_put_contents('public/assets/css/inter-font.css', $interCSS);
    echo "✓ Created Inter font CSS\n";
    
    // Download actual font files (using a simpler approach - download from a reliable source)
    echo "Note: Inter font files are large. You may need to download them manually from:\n";
    echo "https://fonts.google.com/specimen/Inter\n";
    echo "Or use the system font fallback (Inter is similar to system fonts)\n";
}

// Download all resources
echo "\nDownloading CSS and JavaScript files...\n\n";

$successCount = 0;
$failCount = 0;

foreach ($resources as $resource) {
    if (downloadFile($resource['url'], $resource['path'])) {
        $successCount++;
        
        // Fix Font Awesome CSS to use local fonts
        if ($resource['type'] === 'css' && strpos($resource['path'], 'font-awesome') !== false) {
            $content = file_get_contents($resource['path']);
            $content = str_replace(
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/',
                '../fonts/fontawesome/',
                $content
            );
            file_put_contents($resource['path'], $content);
            echo "  → Updated Font Awesome paths to local\n";
        }
    } else {
        $failCount++;
    }
}

// Download fonts
downloadFontAwesomeFonts();
downloadInterFont();

echo "\n========================================\n";
echo "Download Summary\n";
echo "========================================\n";
echo "✓ Successfully downloaded: $successCount files\n";
if ($failCount > 0) {
    echo "✗ Failed: $failCount files\n";
}
echo "\nNext step: Update views/layout/header.php and footer.php to use local files.\n";
echo "Run: php update_local_resources.php\n\n";

