<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

echo "🚀 Test de génération d'un contrat .docx\n\n";

// Charger le fichier texte du contrat
$textTemplatePath = __DIR__ . "/models_contrat/Contrat_prestation_service_model_etude.txt";

if (!file_exists($textTemplatePath)) {
    die("❌ Fichier template non trouvé: {$textTemplatePath}\n");
}

echo "✅ Template trouvé: {$textTemplatePath}\n";

$contractText = file_get_contents($textTemplatePath);
echo "✅ Contenu chargé: " . strlen($contractText) . " caractères\n\n";

// Créer un document PHPWord
$phpWord = new PhpWord();

// Créer une section
$section = $phpWord->addSection([
    'marginLeft' => 1134,
    'marginRight' => 1134,
    'marginTop' => 1700,
    'marginBottom' => 1134,
]);

echo "✅ Section créée\n";

// Ajouter en-tête
$header = $section->addHeader();
$header->addText(
    'ELI-VOYAGES SARL U',
    ['bold' => true, 'size' => 14, 'color' => '1F497D'],
    ['alignment' => 'center']
);
$header->addText(
    'Adidogomé-Kohé, Lomé (Togo) | Tél: +1 (416) 276-8269',
    ['size' => 9, 'color' => '666666'],
    ['alignment' => 'center']
);

echo "✅ En-tête créé\n";

// Ajouter pied de page
$footer = $section->addFooter();
$footer->addText(
    'Page {PAGE} / {NUMPAGES}',
    ['size' => 9],
    ['alignment' => 'center']
);

echo "✅ Pied de page créé\n";

// Ajouter le contenu (simplifié pour le test)
$lines = explode("\n", $contractText);
$lineCount = 0;

foreach ($lines as $line) {
    $line = trim($line);
    
    if (empty($line)) {
        $section->addTextBreak();
        continue;
    }
    
    // Articles
    if (preg_match('/^ARTICLE\s+\d+\s*[–-]/', $line)) {
        $section->addText($line, ['bold' => true, 'size' => 11], ['spaceAfter' => 100]);
        $lineCount++;
        continue;
    }
    
    // Bullet points
    if (preg_match('/^[•\-]\s*(.+)$/', $line, $matches)) {
        $section->addListItem($matches[1], 0, ['size' => 10], ['spaceAfter' => 50]);
        $lineCount++;
        continue;
    }
    
    // Texte normal
    $section->addText($line, ['size' => 10], ['spaceAfter' => 100]);
    $lineCount++;
}

echo "✅ Contenu ajouté: {$lineCount} lignes traitées\n";

// Sauvegarder le document
$outputPath = __DIR__ . "/storage/app/test_contract_etude.docx";

// Créer le dossier s'il n'existe pas
$dir = dirname($outputPath);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
    echo "✅ Répertoire créé: {$dir}\n";
}

$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save($outputPath);

echo "✅ Document sauvegardé: {$outputPath}\n";
echo "📄 Taille du fichier: " . filesize($outputPath) . " octets\n";
echo "\n🎉 SUCCÈS! Le contrat .docx a été généré!\n";
