<?php
// Simple PHP syntax checker
$file = 'c:/xampp1/htdocs/tabulation/views/judge_management/index.php';
$content = file_get_contents($file);

// Count PHP opening and closing tags
$openCount = substr_count($content, '<?php');
$closeCount = substr_count($content, '?>');

echo "PHP opening tags: $openCount\n";
echo "PHP closing tags: $closeCount\n";

if ($openCount !== $closeCount) {
    echo "ERROR: Mismatched PHP tags!\n";
} else {
    echo "PHP tags match: OK\n";
}

// Check for common syntax issues
echo "\nChecking for common syntax issues...\n";

// Check for unclosed braces
$openBraces = substr_count($content, '{');
$closeBraces = substr_count($content, '}');

echo "Open braces: $openBraces\n";
echo "Close braces: $closeBraces\n";

if ($openBraces !== $closeBraces) {
    echo "ERROR: Unclosed braces!\n";
} else {
    echo "Braces match: OK\n";
}

echo "\nDone.\n";
