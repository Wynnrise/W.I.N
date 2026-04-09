<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { die('Not authorised.'); }

$bin = __DIR__ . '/bin/wkhtmltopdf';

echo '<pre style="font-family:monospace;font-size:13px;padding:20px;">';
echo "=== wkhtmltopdf binary test ===\n\n";

if (!file_exists($bin)) {
    echo "❌ Binary not found at: $bin\n";
    echo "Upload the binary to public_html/bin/wkhtmltopdf and try again.\n";
    exit;
}

echo "✅ Binary found at: $bin\n";
echo "File size: " . number_format(filesize($bin)) . " bytes\n\n";

// Make executable
chmod($bin, 0755);
echo "✅ chmod 755 applied\n\n";

// Test version
$version = shell_exec(escapeshellcmd($bin) . ' --version 2>&1');
echo "--- Version output ---\n$version\n";

if (strpos($version, 'wkhtmltopdf') !== false) {
    echo "✅ Binary works!\n\n";

    // Generate a quick test PDF
    $test_html = '/tmp/wynston_test.html';
    $test_pdf  = '/tmp/wynston_test.pdf';
    file_put_contents($test_html, '<html><body><h1 style="color:#002446">Wynston W.I.N Test PDF</h1><p>If you can read this, wkhtmltopdf works on this server.</p></body></html>');

    $cmd = escapeshellcmd($bin) . ' --quiet --no-outline --margin-top 10mm --margin-bottom 10mm --margin-left 10mm --margin-right 10mm ' . escapeshellarg($test_html) . ' ' . escapeshellarg($test_pdf) . ' 2>&1';
    $out = shell_exec($cmd);
    echo "--- Test PDF generation ---\n";
    echo "Command output: " . ($out ?: '(none — good sign)') . "\n";

    if (file_exists($test_pdf) && filesize($test_pdf) > 100) {
        echo "✅ Test PDF generated successfully (" . number_format(filesize($test_pdf)) . " bytes)\n";
        echo "\nwkhtmltopdf is ready. The report generator can now be rewritten.\n";
    } else {
        echo "❌ PDF was not generated. Check the command output above.\n";
    }
} else {
    echo "❌ Binary did not return expected output. May be wrong architecture or corrupted.\n";
    echo "Expected 'wkhtmltopdf' in output, got: $version\n";
}
echo '</pre>';
