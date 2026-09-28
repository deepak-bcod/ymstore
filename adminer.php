<?php
/**
 * Adminer Auto-Downloader
 * Use this file once, then delete it after adminer.php is downloaded.
 */

$adminer_url = "https://github.com/vrana/adminer/releases/download/v4.8.1/adminer-4.8.1.php";
$save_as     = "adminer.php";

echo "<pre>";

echo "Downloading Adminer...\n";

$file = file_get_contents($adminer_url);

if ($file === false) {
    exit("❌ Failed to download Adminer. Check server allow_url_fopen settings.\n");
}

$result = file_put_contents($save_as, $file);

if ($result === false) {
    exit("❌ Failed to save adminer.php. Check folder permissions.\n");
}

echo "✔ Adminer downloaded successfully!\n";
echo "File saved as: {$save_as}\n";
echo "Open it in browser: http://" . $_SERVER['HTTP_HOST'] . "/" . $save_as . "\n";

echo "</pre>";
