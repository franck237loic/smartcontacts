<?php

/**
 * Script pour trouver les opérateurs sans logo
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();

$config = require __DIR__ . '/app/Config/config.php';

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}",
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['options']
    );
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Récupérer tous les opérateurs avec leur logo
$sql = "SELECT id, name, brand, logo FROM operators ORDER BY name";
$stmt = $pdo->query($sql);
$operators = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les fichiers de logos
$logoDir = __DIR__ . '/public/LOGO';
$logoFiles = [];
if (is_dir($logoDir)) {
    $files = scandir($logoDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $logoFiles[strtolower($file)] = $file;
        }
    }
}

// Comparer et trouver les opérateurs sans logo
$operatorsWithoutLogo = [];
$operatorsWithLogo = [];

foreach ($operators as $operator) {
    $logo = $operator['logo'];
    $logoLower = strtolower($logo);
    
    if (empty($logo) || !isset($logoFiles[$logoLower])) {
        $operatorsWithoutLogo[] = [
            'id' => $operator['id'],
            'name' => $operator['name'],
            'brand' => $operator['brand'],
            'logo' => $logo
        ];
    } else {
        $operatorsWithLogo[] = $operator['name'];
    }
}

// Sauvegarder les résultats dans un fichier
$output = "ANALYSE DES LOGOS D'OPERATEURS\n";
$output .= "================================\n\n";
$output .= "STATISTIQUES\n";
$output .= "Total opérateurs: " . count($operators) . "\n";
$output .= "Avec logo: " . count($operatorsWithLogo) . "\n";
$output .= "Sans logo: " . count($operatorsWithoutLogo) . "\n\n";

$output .= "OPERATEURS SANS LOGO (" . count($operatorsWithoutLogo) . ")\n";
$output .= str_repeat("-", 80) . "\n";
$output .= sprintf("%-10s %-50s %-30s %-30s\n", "ID", "Nom", "Marque", "Logo attendu");
$output .= str_repeat("-", 80) . "\n";

foreach ($operatorsWithoutLogo as $op) {
    $output .= sprintf("%-10s %-50s %-30s %-30s\n", 
        $op['id'], 
        substr($op['name'], 0, 50), 
        substr($op['brand'], 0, 30), 
        substr($op['logo'], 0, 30)
    );
}

$output .= "\n\nFICHIERS DE LOGOS DISPONIBLES (" . count($logoFiles) . ")\n";
$output .= str_repeat("-", 80) . "\n";
foreach ($logoFiles as $file) {
    $output .= $file . "\n";
}

file_put_contents(__DIR__ . '/missing_logos_report.txt', $output);
echo "Rapport sauvegardé dans missing_logos_report.txt\n";
echo "Total opérateurs sans logo: " . count($operatorsWithoutLogo) . "\n";
