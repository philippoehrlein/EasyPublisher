<?php
/**
 * EasyPublisher Setup
 * Automatische Installation und Konfiguration
 */

// Grundlegende Konfiguration
define('RELEASE_URL', 'https://api.github.com/repos/philippoehrlein/EasyPublisher/releases/latest');
define('INSTALL_DIR', __DIR__);

// Funktionen
function checkRequirements() {
    $requirements = [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'write permissions' => is_writable(INSTALL_DIR)
    ];
    
    return $requirements;
}

function downloadAndExtract() {
    $zipFile = INSTALL_DIR . '/easypublisher.zip';
    
    // Lade Release herunter
    $ch = curl_init(RELEASE_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $zipContent = curl_exec($ch);
    curl_close($ch);
    file_put_contents($zipFile, $zipContent);
    
    // Entpacke direkt
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo(INSTALL_DIR);
        $zip->close();
        
        // Aufräumen
        unlink($zipFile);
        return true;
    }
    return false;
}

// Hauptprogramm
echo "<h1>EasyPublisher Setup</h1>";

// Prüfe Voraussetzungen
$requirements = checkRequirements();
$allOk = true;

foreach ($requirements as $requirement => $met) {
    echo "<p>" . $requirement . ": " . ($met ? "✓" : "✗") . "</p>";
    if (!$met) $allOk = false;
}

if (!$allOk) {
    echo "<p>Bitte stellen Sie sicher, dass alle Voraussetzungen erfüllt sind.</p>";
    exit;
}

// Installation
try {
    echo "<p>Installiere EasyPublisher...</p>";
    if (downloadAndExtract()) {
        echo "<p>Installation abgeschlossen! Sie können jetzt loslegen.</p>";
    } else {
        echo "<p>Fehler bei der Installation. Bitte versuchen Sie es später erneut.</p>";
    }
} catch (Exception $e) {
    echo "<p>Fehler bei der Installation: " . $e->getMessage() . "</p>";
} 

